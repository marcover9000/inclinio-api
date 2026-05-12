<?php

use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Identity\Domain\Models\User;

it('author can delete their own note', function () {
    $author = User::factory()->staff()->create();
    $note = LeadNote::factory()->create(['author_id' => $author->id]);
    $this->actingAs($author)->deleteJson("/api/notes/{$note->id}")->assertNoContent();
    expect(LeadNote::find($note->id))->toBeNull();
});

it('admin can delete any note', function () {
    $author = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create();
    $note = LeadNote::factory()->create(['author_id' => $author->id]);
    $this->actingAs($admin)->deleteJson("/api/notes/{$note->id}")->assertNoContent();
});

it('staff cannot delete another staff note', function () {
    $a = User::factory()->staff()->create();
    $b = User::factory()->staff()->create();
    $note = LeadNote::factory()->create(['author_id' => $a->id]);
    $this->actingAs($b)->deleteJson("/api/notes/{$note->id}")->assertForbidden();
});
