<?php

/*
 * Test del endpoint de salut de l'API.
 * Comprova que el servei respon amb 200 i un payload JSON ben format.
 * Útil com a smoke test després de cada deploy.
 */

it('respon amb 200 i status ok al endpoint /api/health', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();
    $response->assertJson(['status' => 'ok']);
    $response->assertJsonStructure(['status', 'timestamp']);
});
