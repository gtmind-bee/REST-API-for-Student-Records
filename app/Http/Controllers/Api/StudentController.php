<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StudentIndexRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function index(StudentIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = $request->user()->students();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder->where('admission_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        foreach (['department', 'level', 'status'] as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== '') {
                $query->where($filter, $filters[$filter]);
            }
        }

        $students = $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15)
            ->appends($request->query());

        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $request->user()->students()->create($request->validated());

        return (new StudentResource($student))
            ->response()
            ->setStatusCode(201);
    }

    public function show(\Illuminate\Http\Request $request, int $student): StudentResource
    {
        return new StudentResource($request->user()->students()->findOrFail($student));
    }

    public function update(UpdateStudentRequest $request, int $student): StudentResource
    {
        $record = $request->user()->students()->findOrFail($student);
        $record->update($request->validated());

        return new StudentResource($record->refresh());
    }

    public function destroy(\Illuminate\Http\Request $request, int $student): Response
    {
        $request->user()->students()->findOrFail($student)->delete();

        return response()->noContent();
    }
}
