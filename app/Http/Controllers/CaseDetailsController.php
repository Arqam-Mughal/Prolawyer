<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\BareAct;
use App\Models\CaseImage;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ConnectedMatter;
use App\Models\Organization;
use App\Models\PetitionerAdvocate;
use App\Models\RefTag;
use App\Models\RespondentAdvocate;
use App\Models\Upload;
use App\Models\Worklist;
use App\Services\EncryptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CaseDetailsController extends Controller
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function getCaseDetails($caseId): JsonResponse
    {
        $case = CaseModel::find($caseId);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $notes = $case->notes_description;
        $documentIds = DB::table('uploads')->where('case_id', $case->id)->pluck('id')->toArray();
        $documents = DB::table('uploads')->where('case_id', $case->id)->get(['id', 'filename']);

        $remarks = $case->remarks;
        $worklist = Worklist::where('case_id', $caseId)->get(['title', 'start_date', 'end_date', 'description', 'status']);
        $connectedCases = collect();
        $connectedMatters = ConnectedMatter::where('lawyer_id', $case->lawyer_id)->first();

        if ($connectedMatters) {
            $connectedCaseIds = is_array($connectedMatters->connected_matters) ? $connectedMatters->connected_matters : json_decode($connectedMatters->connected_matters, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($connectedCaseIds)) {
                $connectedCases = CaseModel::select([
                    'cases.id',
                    'cases.case_no',
                    'cases.status',
                    'cases.case_category',
                    DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title")
                ])
                    ->join('petitioners', 'cases.id', '=', 'petitioners.case_id')
                    ->join('respondents', 'cases.id', '=', 'respondents.case_id')
                    ->whereIn('cases.id', $connectedCaseIds)
                    ->get();
            }
        }

        // Fetch petitioners data with advocates
        $petitionersData = json_decode($case->petitioners, true);
        $petitioners = collect($petitionersData)->map(function ($petitioner) use ($caseId) {
            $advocate = PetitionerAdvocate::where('case_id', $caseId)
                ->first(['id', 'petitioner_advocate']);

            return [
                'id' => $petitioner['id'],
                'name' => $petitioner['petitioner'],
                'advocates_id' => $advocate ? $advocate->id : null,
                'advocates_name' => $advocate ? $advocate->petitioner_advocate : null,
            ];
        })->toArray();

        // Fetch respondents data with advocates
        $respondentsData = json_decode($case->respondents, true);
        $respondents = collect($respondentsData)->map(function ($respondent) use ($caseId) {
            $advocate = RespondentAdvocate::where('case_id', $caseId)
                ->first(['id', 'respondent_advocate']);

            return [
                'id' => $respondent['id'],
                'name' => $respondent['respondent'],
                'advocates_id' => $advocate ? $advocate->id : null,
                'advocates_name' => $advocate ? $advocate->respondent_advocate : null,
            ];
        })->toArray();

        // Prepare the basic info array and other data
        $basicInfo = [
            'case_id' => $case->id,
            'case_no' => $case->case_no,
            'brief_number' => $case->Brief_no,
            'cnr' => $case->cnr_no,
            'judge' => $case->judge_name ?? null,
            'case_category' => $case->case_category,
            'case_year' => $case->case_year,
            'room_no' => $case->court_room_no,
            'clients' => $case->clients ? $case->clients->pluck('name')->toArray() : [],
            'brief_for' => $case->brief_for,
            'assigned_to' => $case->assigned_to,
            'petitioners' => $petitioners,
            'respondents' => $respondents,
        ];

        // Prepare case history with court details
        $caseHistory = json_decode($case->history, true);
        if (is_array($caseHistory)) {
            foreach ($caseHistory as &$historyItem) {
                $historyItem['date'] = $historyItem['date'] ?? null;
                $historyItem['next_date'] = $historyItem['next_date'] ?? null;
                $historyItem['status'] = $historyItem['status'] ?? null;
                $historyItem['remarks'] = $historyItem['remarks'] ?? null;
                $historyItem['court'] = $case->court_bench; // Adding court to each history item
            }
        }

        // Prepare orders with PDF link
        $orders = collect(json_decode($case->orders, true))->map(function ($order) {
            return [
                'order_details' => $order,
                'pdf_link' => url('path/to/order/pdf/' . $order['id']) // Adjust URL path as needed
            ];
        })->toArray();

        $labels = $case->labels; // Assuming labels are already defined in $case

        $caseDetails = [
            'labels' => $labels,
            'basic_info' => $basicInfo,
            'history' => $caseHistory,
            'orders' => $orders,
            'notes' => explode("\n", $notes),
            'remarks' => $remarks,
            'decided' => $case->decided_toggle,
            'abandoned' => $case->abbondend_toggle,
            'court_bench' => $case->court_bench,
            'document_ids' => $documentIds,
            'document_links' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'file_link' => url($doc->filename)
                ];
            })->toArray(),
            'worklist' => $worklist,
            'connected_cases' => $connectedCases,
        ];

        // Encrypt case details
        $apiKey = ApiKey::first();
        if (!$apiKey || !$apiKey->case_encryption_key) {
            return response()->json($caseDetails);
        }
        $key = $apiKey->case_encryption_key;

        $caseDetailsJson = json_encode($caseDetails);
        $encryptedCaseDetails = $this->encryptionService->encryptDataToString($caseDetailsJson, $key);

        return response()->json(['data' => $encryptedCaseDetails]);
    }





    //get all organizations
    public function getAllOrganizations(): JsonResponse
    {
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            return response()->json(['message' => 'No organizations found'], 404);
        }

        // Map over the collection to transform each organization
        $transformedOrganizations = $organizations->map(function ($organization) {
            // Decode JSON fields into arrays and handle null cases
            $representators = json_decode($organization->representator, true) ?? [];
            $contacts = json_decode($organization->contact, true) ?? [];
            $emails = json_decode($organization->email, true) ?? [];

            // Combine fields into the 'authorized' array
            $authorized = [];
            foreach ($representators as $index => $name) {
                $authorized[] = [
                    'name' => $name,
                    'email' => $emails[$index] ?? null,
                    'phone' => $contacts[$index] ?? null,
                ];
            }

            // Add 'authorized' key to the organization and remove individual fields
            $organization->authorized = $authorized;
            unset($organization->representator, $organization->contact, $organization->email);

            return $organization;
        });

        return response()->json(['organizations' => $transformedOrganizations]);
    }


    public function getOrganization($organizationId): JsonResponse
    {
        $organization = Organization::find($organizationId);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Decode JSON fields into arrays
        $representators = json_decode($organization->representator, true);
        $contacts = json_decode($organization->contact, true);
        $emails = json_decode($organization->email, true);

        // Combine fields into the 'authorized' array
        $authorized = [];
        foreach ($representators as $index => $name) {
            $authorized[] = [
                'name' => $name,
                'email' => $emails[$index] ?? null,
                'phone' => $contacts[$index] ?? null,
            ];
        }

        // Add 'authorized' key to the organization object and remove individual fields
        $organization->authorized = $authorized;
        unset($organization->representator, $organization->contact, $organization->email);

        // Encrypt and send response
        $apiKey = ApiKey::first();
        if (!$apiKey || !$apiKey->case_encryption_key) {
            return response()->json($organization);
        }

        $key = $apiKey->case_encryption_key;

        $organizationJson      = json_encode($organization);
        $encryptedOrganization = $this->encryptionService->encryptDataToString($organizationJson, $key);

        return response()->json(['data' => $encryptedOrganization]);
    }





    // Update organization details
    public function updateOrganization(Request $request, $organizationId): JsonResponse
    {
        $organization = Organization::find($organizationId);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'adv_id' => 'nullable|integer|exists:advocates,id',
            'organization_name' => 'nullable|string|max:255',
            'representor' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:15',
            'email' => 'nullable|string|email|max:255|unique:organizations,email,' . $organization->id,
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the organization's information
        $organization->update($request->all());

        return response()->json(['message' => 'Organization information updated successfully']);
    }

    public function deleteOrganization($organizationId): JsonResponse
    {
        $organization = Organization::find($organizationId);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);


    }
    public function addOrganization(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'adv_id' => 'nullable|integer',
            'organization_name' => 'required|string|max:255',
            'organization_address' => 'nullable|string|max:255',
            'authorized_person' => 'required|array|min:1',
            'authorized_person.*.name' => 'nullable|string|max:255',
            'authorized_person.*.email' => 'nullable|string|email|max:255|unique:organizations,email',
            'authorized_person.*.phone' => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Extract common organization data
        $advId = $request->input('adv_id');
        $organizationName = $request->input('organization_name');
        $organizationAddress = $request->input('organization_address');

        // Extract the names, emails, and phones into separate arrays
        $representators = array_column($request->input('authorized_person'), 'name');
        $emails = array_column($request->input('authorized_person'), 'email');
        $contacts = array_column($request->input('authorized_person'), 'phone');

        // Create organization entry with each field as an array
        $organization = Organization::create([
            'adv_id' => $advId,
            'organization_name' => $organizationName,
            'address' => $organizationAddress,
            'representator' => json_encode($representators), // Store names as JSON
            'email' => json_encode($emails),                 // Store emails as JSON
            'contact' => json_encode($contacts),             // Store phones as JSON
        ]);

        return response()->json([
            'message' => 'Organization added successfully with authorized persons.',
            'organization' => $organization,
        ]);
    }


    //GET ALL clients
    public function getAllClients(Request $request): JsonResponse
    {
        // Get search query and pagination limit from request
        $searchQuery = $request->get('search', '');  // Default to an empty string if no search query
        $perPage = $request->get('per_page', 10);    // Default to 10 items per page

        // Fetch clients with pagination and search functionality
        $clients = Client::where(function ($query) use ($searchQuery) {
            $query->where('name', 'like', "%{$searchQuery}%")
                ->orWhere('email', 'like', "%{$searchQuery}%");
        })
            ->paginate($perPage);

        // Check if there are any clients
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return paginated clients with search results
        return response()->json($clients);
    }




    public function getClient($clientId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $apiKey = ApiKey::first();
        if (!$apiKey || !$apiKey->case_encryption_key) {
            return response()->json($client);
        }
        $key = $apiKey->case_encryption_key;

        $clientJson      = json_encode($client);
        $encryptedClient = $this->encryptionService->encryptDataToString($clientJson, $key);

        return response()->json(['data' => $encryptedClient]);
    }

    //all tags list
    public function getAllTags(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
        $tags = RefTag::paginate($perPage);

        if ($tags->isEmpty()) {
            return response()->json(['message' => 'No tags found'], 404);
        }

        return response()->json($tags);
    }


    public function getTag($tagId): JsonResponse
    {
        $tag = RefTag::find($tagId);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $apiKey = ApiKey::first();
        if (!$apiKey || !$apiKey->case_encryption_key) {
            return response()->json($tag);
        }
        $key = $apiKey->case_encryption_key;

        $tagJson      = json_encode($tag);
        $encryptedTag = $this->encryptionService->encryptDataToString($tagJson, $key);

        return response()->json(['data' => $encryptedTag]);
    }

    // Add Tag
    public function addTag(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tag = RefTag::create($request->all());

        return response()->json(['message' => 'Tag added successfully', 'tag' => $tag]);
    }

// Update Tag
    public function updateTag(Request $request, $tagId): JsonResponse
    {
        $tag = RefTag::find($tagId);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tag->update($request->all());

        return response()->json(['message' => 'Tag updated successfully', 'tag' => $tag]);
    }

// Delete Tag
    public function deleteTag($tagId): JsonResponse
    {
        $tag = RefTag::find($tagId);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }

    //get all connected matter

    public function getAllConnectedMatters(): JsonResponse
    {
        // Step 1: Retrieve all connected matters
        $connectedMatters = ConnectedMatter::all();

        // Step 2: Check if there are any connected matters
        if ($connectedMatters->isEmpty()) {
            return response()->json(['message' => 'No connected matters found'], 404);
        }

        // Step 3: Prepare response with detailed data for each connected matter
        $response = $connectedMatters->map(function ($connectedMatter) {
            // Decode connected case IDs, ensuring we get an array
            $connectedCaseIds = is_array($connectedMatter->connected_matters)
                ? $connectedMatter->connected_matters
                : (json_decode($connectedMatter->connected_matters, true) ?: []);

            // Fetch details for the primary case
            $primaryCase = CaseModel::select([
                'cases.id',
                'cases.case_no',
                'cases.case_category',
                'cases.case_year',
                DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title"),
                'respondents.respondent',
                'petitioners.petitioner'
            ])
                ->join('petitioners', 'cases.id', '=', 'petitioners.case_id')
                ->join('respondents', 'cases.id', '=', 'respondents.case_id')
                ->where('cases.id', $connectedMatter->primary_case)
                ->first();

            // Skip if primary case not found
            if (!$primaryCase) {
                return null;
            }

            // Fetch details for each connected case
            $connectedCases = CaseModel::select([
                'cases.id',
                'cases.case_no',
                'cases.case_category',
                'cases.case_year',
                DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title"),
                'respondents.respondent',
                'petitioners.petitioner'
            ])
                ->join('petitioners', 'cases.id', '=', 'petitioners.case_id')
                ->join('respondents', 'cases.id', '=', 'respondents.case_id')
                ->whereIn('cases.id', $connectedCaseIds)
                ->get();

            // Skip if no connected cases are found
            if ($connectedCases->isEmpty()) {
                return null;
            }

            // Structure the response data for each connected matter
            return [
                'id' => $connectedMatter->id,
                'primary_case' => $primaryCase,
                'connected_cases' => $connectedCases,
            ];
        })->filter(); // Remove any null entries from the collection

        // Step 4: Return the formatted response
        return response()->json(['connected_matters' => $response->values()]);
    }



    public function getConnectedMatters($caseId): JsonResponse
    {
        // Step 1: Find the connected matters for the given case ID
        $connectedCaseIds = DB::table('connected_matters')
            ->where('primary_case', $caseId)
            ->pluck('connected_matters')
            ->map(function ($connectedMatters) {
                return json_decode($connectedMatters, true);
            })
            ->flatten()
            ->toArray();  // Convert the collection to an array

        // Step 2: Check if connected matters were found
        if (empty($connectedCaseIds)) {
            return response()->json(['message' => 'No connected matters found'], 404);
        }

        // Step 3: Fetch details for the connected cases
        $connectedCases = CaseModel::select([
            'cases.id',
            'cases.case_no',
            'cases.case_category',
            'cases.case_year',
            DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title"),
            'respondents.respondent',
            'petitioners.petitioner'
        ])
            ->join('petitioners', 'cases.id', '=', 'petitioners.case_id')
            ->join('respondents', 'cases.id', '=', 'respondents.case_id')
            ->whereIn('cases.id', $connectedCaseIds)
            ->get();

        // Step 4: Return the response with connected cases
        return response()->json(['connected_cases' => $connectedCases]);
    }

    public function addConnectedMatter(Request $request): JsonResponse
    {
        // Step 1: Validate the incoming request
        $validator = Validator::make($request->all(), [
            'primary_case' => 'required|integer|exists:cases,id',
            'connected_matters' => 'required|array', // Expecting an array of IDs
            'connected_matters.*' => 'integer|exists:cases,id', // Each item must be a valid case ID
        ]);

        // Step 2: If validation fails, return errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Step 3: Find or create the connected matter entry
        $connectedMatter = ConnectedMatter::where('primary_case', $request->primary_case)->first();

        if (!$connectedMatter) {
            // Create a new entry with connected_matters as a JSON array of strings
            $connectedMatter = ConnectedMatter::create([
                'primary_case' => $request->primary_case,
                'connected_matters' => json_encode(array_map('strval', $request->connected_matters)), // Convert IDs to strings
            ]);
        } else {
            // Decode existing connected matters into an array
            $currentConnectedMatters = is_string($connectedMatter->connected_matters)
                ? json_decode($connectedMatter->connected_matters, true)
                : $connectedMatter->connected_matters;

            // Ensure it's an array
            if (!is_array($currentConnectedMatters)) {
                $currentConnectedMatters = [];
            }

            // Convert each new case ID to a string and add it if not already present
            foreach ($request->connected_matters as $newMatter) {
                $newMatterStr = (string)$newMatter;
                if (!in_array($newMatterStr, $currentConnectedMatters)) {
                    $currentConnectedMatters[] = $newMatterStr;
                }
            }

            // Update the connected matters field as a JSON array
            $connectedMatter->connected_matters = array_values(array_unique($currentConnectedMatters)); // Ensure uniqueness
            $connectedMatter->save();
        }

        // Step 5: Return the response with the updated connected matter data
        return response()->json([
            'message' => 'Connected matter updated successfully',
            'connected_matter' => $connectedMatter
        ], 201);
    }



    public function deleteConnectedMatter($connectedMatterId): JsonResponse
    {
        $connectedMatter = ConnectedMatter::find($connectedMatterId);


        if (!$connectedMatter) {
            return response()->json(['message' => 'Connected matter not found'], 404);
        }

        $connectedMatter->delete();

        return response()->json(['message' => 'Connected matter deleted successfully']);
    }

    public function updateConnectedMatter(Request $request, $connectedMatterId): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'primary_case' => 'required|integer|exists:cases,id',  // Ensure primary_case is a valid case ID from the cases table
            'connected_matters' => 'required|array|min:1',  // Ensure connected_matters is a non-empty array
            'connected_matters.*' => 'integer|exists:cases,id'  // Validate each element in the array as an integer and valid case ID
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the connected matter by its ID
        $connectedMatter = ConnectedMatter::find($connectedMatterId);

        // If the connected matter is not found, return a 404 response
        if (!$connectedMatter) {
            return response()->json(['message' => 'Connected matter not found'], 404);
        }

        // Convert the connected_matters array to strings and store it as JSON
        $newConnectedMatters = array_map('strval', $request->input('connected_matters'));

        // Update the connected matter with the new data
        $connectedMatter->update([
            'primary_case' => $request->input('primary_case'),
            'connected_matters' => json_encode($newConnectedMatters),  // Store as a JSON array of strings
        ]);

        // Return a success message after the update
        return response()->json([
            'message' => 'Connected matter updated successfully',
            'connected_matter' => $connectedMatter
        ]);
    }


    public function addClient(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email',
            'mobile' => 'required|string|size:10|regex:/^\d{10}$/|unique:clients,mobile',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new client
        $client = Client::create($request->all());

        return response()->json(['message' => 'Client added successfully', 'client' => $client]);
    }
    public function updateClient(Request $request, $clientId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:clients,email,' . $client->id,
            'gender' => 'nullable|string|max:10',
            'mobile' => 'nullable|string|size:10|regex:/^\d{10}$/|unique:clients,mobile,' . $client->id,
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'country_id' => 'nullable|integer|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $client->update($request->all());

        return response()->json(['message' => 'Client information updated successfully']);
    }


    public function deleteClient($clientId): JsonResponse
    {
        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);

    }

    public function markAsFavorite($caseId): JsonResponse
    {
        $case = CaseModel::find($caseId);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $case->favourite_case = true;
        $case->save();

        return response()->json(['message' => 'Case marked as favorite successfully']);
    }

    public function markAsCompleted($caseId): JsonResponse
    {
        $case = CaseModel::find($caseId);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $case->completed_case = true;
        $case->in_progress_case = false; // Ensure it's not in progress anymore
        $case->save();

        return response()->json(['message' => 'Case marked as completed successfully']);
    }

    public function markAsInProgress($caseId): JsonResponse
    {
        $case = CaseModel::find($caseId);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $case->in_progress_case = true;
        $case->completed_case = false; // Ensure it's not marked as completed
        $case->save();

        return response()->json(['message' => 'Case marked as in progress successfully']);
    }

    public function unmarkAsFavorite($caseId): JsonResponse
    {
        $case = CaseModel::find($caseId);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $case->favourite_case = false;
        $case->save();

        return response()->json(['message' => 'Case unmarked as favorite successfully']);
    }


    //bare act api
    public function getBareAct($id): JsonResponse
    {
        $bareAct = BareAct::find($id);

        if (!$bareAct) {
            return response()->json(['message' => 'Bare act not found'], 404);
        }

        return response()->json($bareAct);
    }

    public function getBareActsDetails(Request $request): JsonResponse
    {
        $query = BareAct::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', '%' . $search . '%');
        }

        $bareActs = $query->get();

        return response()->json($bareActs);
    }

    public function getAssignedCases($user_id)
    {
        // Retrieve cases assigned to the given user_id
        $cases = CaseModel::where('assigned_to', $user_id)->get();

        if ($cases->isEmpty()) {
            return response()->json(['message' => 'No cases assigned to this user.'], 404);
        }

        return response()->json($cases);
    }

    public function updateAssignedTo(Request $request, $case_id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the case by case_id
        $case = CaseModel::find($case_id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        // Update the assigned_to field
        $case->assigned_to = $request->assigned_to;
        $case->save();

        return response()->json(['message' => 'Case assigned to user successfully.']);
    }

    //add document in case
    public function addDocument(Request $request, $caseId): JsonResponse
    {
        // Validate the case ID from the URL
        $caseExists = \DB::table('cases')->where('id', $caseId)->exists();
        if (!$caseExists) {
            return response()->json(['message' => 'Invalid case ID.'], 404);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'hearing_date_id' => 'nullable|integer|exists:hearing_dates,id', // Optional hearing date ID
            'document' => 'required|file|mimes:pdf,docx,jpeg,png|max:2048', // Validate the uploaded file
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Retrieve the authenticated user's ID
        $userId = $request->user()->id;

        // Handle the file upload
        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName(); // Unique filename
        $filepath = 'uploads/case-file/' . $filename; // Desired file path format

        // Store the file in the specified path
        $file->storeAs('public/uploads/case-file', $filename); // Store the file

        // Generate the full URL to access the file
        $fullUrl = asset('storage/' . $filepath);

        // Create a new document in the uploads table
        $document = Upload::create([
            'case_id' => $caseId, // Get the case ID from the URL
            'user_id' => $userId,
            'hearing_date_id' => $request->input('hearing_date_id'),
            'user_filename' => $file->getClientOriginalName(), // Original filename uploaded by the user
            'filename' => $fullUrl, // Saved filename
            'filepath' => $filepath, // Full URL to access the file
            'file_type' => $file->getClientOriginalExtension(), // File type
            'uuid' => (string) Str::uuid(), // Generate a UUID for the document
        ]);

        return response()->json(['message' => 'Document added successfully', 'document' => $document]);
    }

    //delete document
    public function deleteDocument($documentId): JsonResponse
    {
        // Find the document by its ID
        $document = Upload::find($documentId);

        // If the document is not found, return a 404 response
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Delete the file from the storage
        \Storage::delete('public/' . $document->filepath);

        // Delete the document from the database
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

}
