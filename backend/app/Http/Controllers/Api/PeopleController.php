<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Person\StorePersonRequest;
use App\Http\Requests\Person\UpdatePersonRequest;
use App\Http\Resources\PersonResource;
use App\Services\PeopleService;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function __construct(
        private PeopleService $service,
    ) {
        $this->authorizeResource(\App\Models\Person::class, 'person');
    }

    public function index(Request $request)
    {
        $filters = $request->only(['workspace_id', 'department_id', 'status', 'search']);
        $people = $this->service->list($filters);

        return PersonResource::collection($people);
    }

    public function show(string $id)
    {
        $person = $this->service->findById($id);

        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        return new PersonResource($person);
    }

    public function store(StorePersonRequest $request)
    {
        $person = $this->service->create($request->validated());

        return response()
            ->json(new PersonResource($person), 201)
            ->header('Location', route('api.people.show', $person));
    }

    public function update(UpdatePersonRequest $request, string $id)
    {
        $person = $this->service->findById($id);

        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        $person = $this->service->update($person, $request->validated());

        return new PersonResource($person);
    }

    public function destroy(string $id)
    {
        $person = $this->service->findById($id);

        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        $this->service->delete($person);

        return response()->json(null, 204);
    }
}