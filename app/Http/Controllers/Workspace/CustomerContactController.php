<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreCustomerContactRequest;
use App\Models\Customer;
use App\Services\VisitContactResolver;
use Illuminate\Http\JsonResponse;

class CustomerContactController extends Controller
{
    public function store(StoreCustomerContactRequest $request, Customer $customer): JsonResponse
    {
        $contact = VisitContactResolver::createContact(
            $customer->id,
            $request->validated('name'),
            $request->validated('phone'),
            $request->validated('position'),
        );

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'label' => $contact->listLabel(),
            ],
        ], 201);
    }
}
