<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Member;
use App\Models\Group;
use App\Models\Product;
use App\Models\Order;
use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;

class ModelRelationshipTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Member has many Groups relationship
     */
    public function testMemberBelongsToManyGroups()
    {
        $member = factory(Member::class)->create();
        $group = factory(Group::class)->create();

        $member->groups()->attach($group->id);

        $this->assertCount(1, $member->groups);
        $this->assertEquals($group->id, $member->groups->first()->id);
    }

    /**
     * Test Member has many Orders relationship
     */
    public function testMemberHasManyOrders()
    {
        $member = factory(Member::class)->create();
        $product = factory(Product::class)->create();
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\Models\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertCount(1, $member->orders);
        $this->assertEquals($order->id, $member->orders->first()->id);
    }

    /**
     * Test Member has many InvoiceLines relationship
     */
    public function testMemberHasManyInvoiceLines()
    {
        $member = factory(Member::class)->create();
        $invoiceLine = factory(InvoiceLine::class)->create([
            'member_id' => $member->id,
        ]);

        $this->assertCount(1, $member->invoice_lines);
        $this->assertEquals($invoiceLine->id, $member->invoice_lines->first()->id);
    }

    /**
     * Test Group belongs to many Members relationship
     */
    public function testGroupBelongsToManyMembers()
    {
        $group = factory(Group::class)->create();
        $member = factory(Member::class)->create();

        $group->members()->attach($member->id);

        $this->assertCount(1, $group->members);
        $this->assertEquals($member->id, $group->members->first()->id);
    }

    /**
     * Test Group has many Orders relationship
     */
    public function testGroupHasManyOrders()
    {
        $group = factory(Group::class)->create();
        $product = factory(Product::class)->create();
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\Models\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertCount(1, $group->orders);
        $this->assertEquals($order->id, $group->orders->first()->id);
    }

    /**
     * Test Order belongs to Product relationship
     */
    public function testOrderBelongsToProduct()
    {
        $product = factory(Product::class)->create(['name' => 'Test Beer']);
        $order = factory(Order::class)->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $order->product);
        $this->assertEquals('Test Beer', $order->product->name);
    }

    /**
     * Test Order polymorphic ownerable relationship (Member)
     */
    public function testOrderPolymorphicOwnerableMember()
    {
        $member = factory(Member::class)->create();
        $product = factory(Product::class)->create();
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\Models\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertInstanceOf(Member::class, $order->ownerable);
        $this->assertEquals($member->id, $order->ownerable->id);
    }

    /**
     * Test Order polymorphic ownerable relationship (Group)
     */
    public function testOrderPolymorphicOwnerableGroup()
    {
        $group = factory(Group::class)->create();
        $product = factory(Product::class)->create();
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\Models\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertInstanceOf(Group::class, $order->ownerable);
        $this->assertEquals($group->id, $order->ownerable->id);
    }

    /**
     * Test Order belongs to InvoiceGroup relationship
     */
    public function testOrderBelongsToInvoiceGroup()
    {
        $invoiceGroup = factory(InvoiceGroup::class)->create(['name' => 'January 2025']);
        $order = factory(Order::class)->create(['invoice_group_id' => $invoiceGroup->id]);

        $this->assertInstanceOf(InvoiceGroup::class, $order->invoice_group);
        $this->assertEquals('January 2025', $order->invoice_group->name);
    }

    /**
     * Test InvoiceProduct belongs to InvoiceGroup
     */
    public function testInvoiceProductBelongsToInvoiceGroup()
    {
        $invoiceGroup = factory(InvoiceGroup::class)->create();
        $invoiceProduct = factory(InvoiceProduct::class)->create([
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertInstanceOf(InvoiceGroup::class, $invoiceProduct->invoice_group);
        $this->assertEquals($invoiceGroup->id, $invoiceProduct->invoice_group->id);
    }

    /**
     * Test InvoiceProductPrice belongs to InvoiceProduct
     */
    public function testInvoiceProductPriceBelongsToInvoiceProduct()
    {
        $invoiceProduct = factory(InvoiceProduct::class)->create();
        $invoiceProductPrice = factory(InvoiceProductPrice::class)->create([
            'invoice_product_id' => $invoiceProduct->id,
        ]);

        $this->assertInstanceOf(InvoiceProduct::class, $invoiceProductPrice->invoice_product);
        $this->assertEquals($invoiceProduct->id, $invoiceProductPrice->invoice_product->id);
    }

    /**
     * Test InvoiceLine belongs to Member
     */
    public function testInvoiceLineBelongsToMember()
    {
        $member = factory(Member::class)->create();
        $invoiceLine = factory(InvoiceLine::class)->create([
            'member_id' => $member->id,
        ]);

        $this->assertInstanceOf(Member::class, $invoiceLine->member);
        $this->assertEquals($member->id, $invoiceLine->member->id);
    }

    /**
     * Test InvoiceLine belongs to InvoiceProductPrice
     */
    public function testInvoiceLineBelongsToInvoiceProductPrice()
    {
        $invoiceProductPrice = factory(InvoiceProductPrice::class)->create();
        $invoiceLine = factory(InvoiceLine::class)->create([
            'invoice_product_price_id' => $invoiceProductPrice->id,
        ]);

        $this->assertInstanceOf(InvoiceProductPrice::class, $invoiceLine->invoice_product_price);
        $this->assertEquals($invoiceProductPrice->id, $invoiceLine->invoice_product_price->id);
    }

    /**
     * Test Member Frst scope (first collection)
     */
    public function testMemberFrstScope()
    {
        $memberFirst = factory(Member::class)->create(['had_collection' => false]);
        $memberRecurring = factory(Member::class)->create(['had_collection' => true]);

        $firstMembers = Member::frst()->get();

        $this->assertTrue($firstMembers->contains($memberFirst->id));
        $this->assertFalse($firstMembers->contains($memberRecurring->id));
    }

    /**
     * Test Member Rcur scope (recurring collection)
     */
    public function testMemberRcurScope()
    {
        $memberFirst = factory(Member::class)->create(['had_collection' => false]);
        $memberRecurring = factory(Member::class)->create(['had_collection' => true]);

        $recurringMembers = Member::rcur()->get();

        $this->assertFalse($recurringMembers->contains($memberFirst->id));
        $this->assertTrue($recurringMembers->contains($memberRecurring->id));
    }

    /**
     * Test Member getFullName method
     */
    public function testMemberGetFullName()
    {
        $member = new Member();
        $member->firstname = 'John';
        $member->lastname = 'Doe';

        $fullName = $member->getFullName();

        $this->assertEquals('John Doe', $fullName);
    }
}
