<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $this->getJson('/api/leads')->assertUnauthorized();
});

it('returns paginated leads', function () {
    Lead::factory()->count(25)->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/leads');
    $resp->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    expect(count($resp->json('data')))->toEqual(20);
});

it('filters by status csv', function () {
    Lead::factory()->withStatus(LeadStatus::New)->create();
    Lead::factory()->withStatus(LeadStatus::Won)->create();
    Lead::factory()->withStatus(LeadStatus::Lost)->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/leads?status=new,won');
    expect(count($resp->json('data')))->toEqual(2);
});

it('filters by tag', function () {
    Lead::factory()->withTag('web')->create();
    Lead::factory()->withTag('seo')->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/leads?tags=web');
    expect(count($resp->json('data')))->toEqual(1);
});

it('searches by person first_name', function () {
    $a = Lead::factory()->create();
    $a->person->update(['first_name' => 'Marc']);
    Lead::factory()->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/leads?search=Marc');
    expect(count($resp->json('data')))->toEqual(1);
});

it('excludes soft-deleted by default', function () {
    $lead = Lead::factory()->create();
    $lead->delete();
    Lead::factory()->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/leads');
    expect(count($resp->json('data')))->toEqual(1);
});

it('returns most recent first by status_changed_at', function () {
    $old = Lead::factory()->create(['status_changed_at' => now()->subDays(2)]);
    $new = Lead::factory()->create(['status_changed_at' => now()]);
    $resp = $this->actingAs($this->admin)->getJson('/api/leads');
    expect($resp->json('data.0.id'))->toEqual($new->id);
});
