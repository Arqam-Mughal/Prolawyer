<?php

namespace App\Http\Controllers\API;

use App\Models\CaseModel;
use App\Models\Worklist;
use Illuminate\Http\Request;
use App\Models\WorklistCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WorklistCategoryResource;

class WorklistCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the status from the request, default to null if not provided
        $status = $request->query('status');

        // Query the Worklist based on the status
        $query = Worklist::query();
        if (!is_null($status)) {
            $query->where('status', $status);
        }

        // Paginate the results
        $worklist_categories = $query->paginate(10);

        // Transform the collection to add petitioners and respondents
        $worklist_categories->getCollection()->transform(function ($worklist) {
            $case = CaseModel::find($worklist->case_id);
            if ($case) {
                $worklist->petitioners = $case->petitioners->pluck('petitioner')->map(function ($name) {
                    return   $name;
                })->implode(', ');
                $worklist->respondents = $case->respondents->pluck('respondent')->map(function ($name) {
                    return  $name;
                })->implode(', ');
            } else {
                $worklist->error = 'Case not found for ID: ' . $worklist->case_id;
            }
            return $worklist;
        });

        return $worklist_categories;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|integer|exists:cases,id',  // Make sure the case exists
            'description' => 'required|string',               // Description is required
            'endDate' => 'nullable|date',                     // End date, nullable
            'assignedTo' => 'nullable|integer|exists:users,id',// Assigned to another user, optional
            'title' => 'nullable|string|max:255',             // Optional fields
            'category_id' => 'nullable|integer',
            'repeated_options' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'set_time' => 'nullable',
            'end_option' => 'nullable|integer',
            'weekdays' => 'nullable|string',
            'end_occurrences' => 'nullable|integer',
        ]);

        // Step 2: If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Step 3: Create the new Worklist, mapping 'assignedTo' to 'user_id'
        $worklist = Worklist::create([
            'case_id' => $request->case_id,
            'description' => $request->description,
            'endDate' => $request->endDate,
            'user_id' => $request->assignedTo,  // Map 'assignedTo' to 'user_id'
            'title' => $request->title,
            'repeated_options' => $request->repeated_options,
            'start_date' => $request->start_date,
            'set_time' => $request->set_time,
            'end_option' => $request->end_option,
            'weekdays' => $request->weekdays,
            'status' => 0,
        ]);

        // Step 4: Return success response with the created worklist data
        return response()->json([
            'success' => true,
            'message' => 'Worklist created successfully.',
            'worklist' => $worklist,
        ], 201); // 201 Created
    }



    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        // Find the worklist entry by its ID
        $worklistEntry = Worklist::find($id);

        // Check if the worklist entry was found
        if (!$worklistEntry) {
            return response()->json(['message' => 'Worklist entry not found'], 404);
        }

        // Add petitioners and respondents to the worklist entry
        $case = CaseModel::find($worklistEntry->case_id);
        if ($case) {
            $worklistEntry->petitioners = $case->petitioners->pluck('petitioner')->toArray();
            $worklistEntry->respondents = $case->respondents->pluck('respondent')->toArray();
        } else {
            $worklistEntry->error = 'Case not found for ID: ' . $worklistEntry->case_id;
        }

        // Return the worklist entry along with its details
        return response()->json($worklistEntry);
    }
    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'assignedTo' => 'required|integer|exists:users,id', // Use 'assignedTo' instead of 'user_id' in the request
            'case_id' => 'required|integer|exists:cases,id',    // Ensure the case exists
            'description' => 'required|string',                 // Description is required
            'title' => 'nullable|string|max:255',               // Optional fields
            'repeated_options' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'set_time' => 'nullable',
            'end_option' => 'nullable|integer',
            'weekdays' => 'nullable|string',
            'end_date' => 'nullable|date',
            'status' => 'nullable|integer',
        ]);

        // Step 2: If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Step 3: Find the existing Worklist
        $worklist = Worklist::find($id);

        // Step 4: Check if the Worklist exists
        if (!$worklist) {
            return response()->json(['error' => 'Worklist not found'], 404);
        }

        // Step 5: Update the worklist with new data, mapping 'assignedTo' to 'user_id'
        $worklist->update([
            'user_id' => $request->assignedTo,     // Map 'assignedTo' to 'user_id'
            'case_id' => $request->case_id,
            'description' => $request->description,
            'title' => $request->title,
            'repeated_options' => $request->repeated_options,
            'start_date' => $request->start_date,
            'set_time' => $request->set_time,
            'end_option' => $request->end_option,
            'weekdays' => $request->weekdays,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ]);

        // Step 6: Return success response with updated worklist
        return response()->json([
            'success' => true,
            'message' => 'Worklist updated successfully.',
            'worklist' => $worklist,
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy($id)
    {
        $worklist = Worklist::find($id);

        if (!$worklist) {
            return response()->json(['error' => 'Worklist not found'], 404);
        }

        $worklist->delete();

        // Step 4: Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Worklist deleted successfully.'
        ], 200);
    }

    public function markAsWorkCompleted($id)
    {
        $worklist = Worklist::find($id);

        if (!$worklist) {
            return response()->json(['message' => 'Worklist not found'], 404);
        }

        $worklist->update(['status' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Worklist marked as completed successfully.',
        ], 200);
    }

    public function markAsWorkInprogress($id)
    {
        $worklist = Worklist::find($id);

        if (!$worklist) {
            return response()->json(['message' => 'Worklist not found'], 404);
        }

        $worklist->update(['status' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Worklist marked as in-progress successfully.',
        ], 200);
    }






}
