<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class patientController extends Controller
{
    //
    public function index()
    {
        try {
            $page = request('page') ?? 1;
            $q = request('q') ?? '';

            // $patients = Patient::where('name', 'like', "%{$q}%")->paginate(10, $page);
            // $patients = Patient::all();
            $patients = Patient::paginate(10, ['*'], 'page', $page);

            if ($q) {
                $patients = Patient::where('name', 'like', "%{$q}%")->paginate(10, ['*'], 'page', $page);
            }

            if ($patients->isEmpty()) {
                $data = [
                    'status' => 200, // Or adjust based on your logic for no results
                    'msg' => 'No patients found matching search term: ' . $q,
                    'patients' => [], // Set an empty array for patients
                    'total' => 0, // Set total to 0
                    'page' => $page,
                    'pages' => $patients->lastPage(),
                    'limit' => 10
                ];

                return response()->json($data, 200);
            }

            $data = [
                'status' => 200,
                'msg' => 'Patients matching search term: ' . $q,
                'patients' => $patients->items(),
                // 'total' => $patients->total(),
                'total' => 10,
                'page' => $page,
                'pages' => $patients->lastPage(),
                'limit' => 10
            ];

            // dd($data);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:patient,email',
            'phone' => 'required',
            'address' => 'required',
            'age' => '',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        $patient = Patient::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'description' => $request->description,
            'age' => $request->age
        ]);

        $data = [
            'status' => 200,
            'message' => 'Patient created successfully',
            'data' => $patient
        ];

        return response()->json($data, 200);
    }

    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            $data = [
                'status' => 404,
                'message' => 'Patient not found'
            ];

            return response()->json($data, 404);
        }

        $data = [
            'status' => 200,
            'message' => 'Patient found',
            'data' => $patient
        ];

        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            $data = [
                'status' => 404,
                'message' => 'Patient not found'
            ];

            return response()->json($data, 404);
        }

        $patient->delete();

        $data = [
            'status' => 200,
            'message' => 'Patient deleted successfully'
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            $data = [
                'status' => 404,
                'message' => 'Patient not found'
            ];

            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:patient,email',
            'phone' => 'required',
            'address' => 'required',
            'description' => 'required',
            'age' => ''
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        $patient->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'description' => $request->description,
            'age' => $request->age
        ]);

        $data = [
            'status' => 200,
            'message' => 'Patient updated successfully',
            'data' => $patient
        ];

        return response()->json($data, 200);
    }

    public function updatePartial(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            $data = [
                'status' => 404,
                'message' => 'Patient not found'
            ];

            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            // 'email' => 'email|unique:patient,email',
            'phone' => '',
            'address' => '',
            'age' => '',
            'description' => '',
            'age' => '',
            'doctor_id' => ''
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        if ($request->has('name')) {
            $patient->name = $request->name;
        }

        if ($request->has('age')) {
            $patient->age = $request->age;
        }

        // if ($request->has('email')) {
        //     $patient->email = $request->email;
        // }

        if ($request->has('phone')) {
            $patient->phone = $request->phone;
        }

        if ($request->has('address')) {
            $patient->address = $request->address;
        }

        if ($request->has('description')) {
            $patient->description = $request->description;
        }

        if ($request->has('age')) {
            $patient->age = $request->age;
        }

        if ($request->has('doctor_id')) {
            $patient->doctor_id = $request->doctor_id;
        }

        $patient->save();

        $data = [
            'status' => 200,
            'message' => 'Patient updated successfully',
            'data' => $patient
        ];

        return response()->json($data, 200);
    }
}
