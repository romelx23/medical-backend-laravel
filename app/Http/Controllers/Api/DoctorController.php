<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index()
    {
        $page = request('page') ?? 1;
        $q = request('q') ?? '';

        // $patients = Doctor::where('name', 'like', "%{$q}%")->paginate(10, $page);
        // $patients = Doctor::all();
        $patients = Doctor::paginate(10);

        if ($q) {
            $patients = Doctor::where('name', 'like', "%{$q}%")->paginate(10);
        }

        if ($patients->isEmpty()) {
            $data = [
                'status' => 200, // Or adjust based on your logic for no results
                'msg' => 'No patients found matching search term: ' . $q,
                'patients' => [], // Set an empty array for patients
                'total' => 0, // Set total to 0
                'page' => $page,
                'limit' => 10
            ];

            return response()->json($data, 200);
        }

        $data = [
            'status' => 200,
            'msg' => 'Doctors matching search term: ' . $q,
            'patients' => $patients->items(),
            // 'total' => $patients->total(),
            'total' => 10,
            'page' => $page,
            'limit' => 10
        ];

        // dd($data);

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'string|max:255',
                'email' => 'required|email|unique:doctor,email',
                'specialization' => 'required',
                'sub_specialization' => 'required',
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 400,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ];

                return response()->json($data, 400);
            }

            $doctor = Doctor::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'specialization' => $request->specialization,
                'sub_specialization' => $request->sub_specialization,
            ]);

            $data = [
                'status' => 200,
                'message' => 'Doctor created successfully',
                'data' => $doctor
            ];

            return response()->json($data, 200);
        } catch (Exception $e) {
            // Log the error for debugging
            dd($e->getMessage());
            Log::error('Failed to create doctor: ' . $e->getMessage());

            // Return a generic error response
            $data = [
                'status' => 500,
                'message' => 'Internal server error'
            ];

            return response()->json($data, 500);
        }
    }

    public function show($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            $data = [
                'status' => 404,
                'message' => 'Doctor not found'
            ];

            return response()->json($data, 404);
        }

        $data = [
            'status' => 200,
            'message' => 'Doctor found',
            'data' => $doctor
        ];

        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            $data = [
                'status' => 404,
                'message' => 'Doctor not found'
            ];

            return response()->json($data, 404);
        }

        $doctor->delete();

        $data = [
            'status' => 200,
            'message' => 'Doctor deleted successfully'
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            $data = [
                'status' => 404,
                'message' => 'Doctor not found'
            ];

            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'string|max:255',
            // 'email' => 'required|email|unique:doctor,email',
            'specialization' => 'required',
            'sub_specialization' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        $doctor->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            // 'email' => $request->email,
            'specialization' => $request->specialization,
            'sub_specialization' => $request->sub_specialization,
        ]);

        $data = [
            'status' => 200,
            'message' => 'Doctor updated successfully',
            'data' => $doctor
        ];

        return response()->json($data, 200);
    }

    public function updatePartial(Request $request, $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            $data = [
                'status' => 404,
                'message' => 'Doctor not found'
            ];

            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'last_name' => 'string|max:255',
            // 'phone' => 'string|max:255',
            'phone' => '',
            // 'email' => 'email|unique:doctor,email',
            'specialization' => '',
            'sub_specialization' => '',
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
            $doctor->name = $request->name;
        }

        if ($request->has('last_name')) {
            $doctor->last_name = $request->last_name;
        }

        // if ($request->has('email')) {
        //     $doctor->email = $request->email;
        // }

        if ($request->has('phone')) {
            $doctor->phone = $request->phone;
        }

        if ($request->has('specialization')) {
            $doctor->specialization = $request->specialization;
        }

        if ($request->has('sub_specialization')) {
            $doctor->sub_specialization = $request->sub_specialization;
        }

        $doctor->save();

        $data = [
            'status' => 200,
            'message' => 'Doctor updated successfully',
            'data' => $doctor
        ];

        return response()->json($data, 200);
    }
}
