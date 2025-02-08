<?php

namespace App\Http\Controllers\API;

use App\Models\Worklist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorklistResource;
use Illuminate\Support\Facades\Validator;

class WorklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $worklists = Worklist::paginate(10);


        return WorklistResource::collection($worklists);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'description' => 'nullable|string',
            'case_id' => 'nullable|integer',
            'repeated_options' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'set_time' => 'nullable|date_format:H:i:s',
            'end_option' => 'nullable|integer',
            'weekdays' => 'nullable|string|max:255',
            'end_date' => 'nullable|date',
            'end_occurrences' => 'nullable|integer',
            'passed_occurrences' => 'nullable|integer',
            'last_occurred' => 'nullable|date',
            'status' => 'required|integer',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $worklist = Worklist::create($request->only([
            'user_id',
            'title',
            'category_id',
            'description',
            'case_id',
            'repeated_options',
            'start_date',
            'set_time',
            'end_option',
            'weekdays',
            'end_date',
            'end_occurrences',
            'passed_occurrences',
            'last_occurred',
            'status',
        ]));


        return response()->json([
            'success' => true,
            'message' => 'Worklist created successfully.',
            'worklist' => new WorklistResource($worklist)
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Worklist $worklist)
    {
        return new WorklistResource($worklist);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Worklist $worklist)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'integer',
            'title' => 'string|max:255',
            'category_id' => 'integer',
            'description' => 'string',
            'case_id' => 'integer',
            'repeated_options' => 'integer',
            'start_date' => 'date',
            'set_time' => 'date_format:H:i:s',
            'end_option' => 'integer',
            'weekdays' => 'string|max:255',
            'end_date' => 'date',
            'end_occurrences' => 'integer',
            'passed_occurrences' => 'integer',
            'last_occurred' => 'date',
            'status' => 'integer',
        ]);
        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $worklist->update($request->only([
            'user_id',
            'title',
            'category_id',
            'description',
            'case_id',
            'repeated_options',
            'start_date',
            'set_time',
            'end_option',
            'weekdays',
            'end_date',
            'end_occurrences',
            'passed_occurrences',
            'last_occurred',
            'status',
        ]));


        return response()->json([
            'success' => true,
            'message' => 'Worklist updated successfully.',
            'worklist' => new WorklistResource($worklist)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Worklist $worklist)
    {
        $worklist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Worklist deleted successfully.'
        ], 200);
    }


    /**
     * Mark a worklist as completed.
     *
     * @param  \App\Models\Worklist  $worklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted(Worklist $worklist)
    {
        $worklist->update(['status' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Worklist marked as completed successfully.',
            'worklist' => new WorklistResource($worklist)
        ], 200);
    }


        /**
         * Mark a worklist as incomplete.
         *
         * @param  \App\Models\Worklist  $worklist
         * @return \Illuminate\Http\JsonResponse
         */
        public function markAsIncomplete(Worklist $worklist)
        {
            $worklist->update(['status' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Worklist marked as incomplete successfully.',
                'worklist' => new WorklistResource($worklist)
            ], 200);
        }
}
