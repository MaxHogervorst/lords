<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly ProductRepository $productRepository,
        private readonly InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $members = $this->memberRepository->all();

        return view('member.index')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $member = $this->memberRepository->create([
            'firstname' => $validated['name'],
            'lastname' => $validated['lastname'],
        ]);

        return response()->json([
            'success' => true,
            'id' => $member->id,
            'firstname' => $member->firstname,
            'lastname' => $member->lastname
        ]);
    }

    public function show(string $id): View
    {
        $member = $this->memberRepository->find((int) $id);
        $products = $this->productRepository->all();
        $currentmonth = $this->invoiceRepository->getCurrentMonth();

        return view('member.order')
            ->with('member', $member)
            ->with('products', $products)
            ->with('currentmonth', $currentmonth);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $member = $this->memberRepository->find((int) $id);

        return view('member.edit')->with('member', $member);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMemberRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        $member = $this->memberRepository->find((int) $id);

        $member = $this->memberRepository->update($member, [
            'firstname' => $validated['name'],
            'lastname' => $validated['lastname'],
            'bic' => $validated['bic'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'had_collection' => $request->has('had_collection'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Succesfully edited' . $member->getFullName()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $member = $this->memberRepository->find((int) $id);
        $fullName = $member->getFullName();
        $this->memberRepository->delete($member);

        return response()->json([
            'success' => true,
            'message' => 'Succesfully deleted' . $fullName
        ]);
    }
}
