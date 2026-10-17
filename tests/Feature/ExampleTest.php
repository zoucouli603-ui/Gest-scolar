<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('manifest.json', false)
            ->assertSee("navigator.serviceWorker.register('", false)
            ->assertSee('sw.js', false)
            ->assertSee('Élèves, enseignants, emplois du temps, notes et finances réunis dans un seul espace professionnel')
            ->assertSee('Créer l’espace de mon école')
            ->assertSee('Se connecter')
            ->assertSee('Notes & bulletins')
            ->assertSee('href="#tarifs"', false)
            ->assertSee('25 000')
            ->assertSee('30 000')
            ->assertSee('20 000')
            ->assertSee('Éveil &amp; Fondamental', false)
            ->assertSee('Parcours Excellence')
            ->assertSee('Fondamental')
            ->assertSee('Cap Lycée')
            ->assertSee('Maternelle · 1er Cycle · 2è Cycle')
            ->assertSee('1er Cycle · 2è Cycle · Lycée')
            ->assertSee('Tous les outils nécessaires pour piloter votre établissement secondaire.')
            ->assertDontSee('Nos abonnements')
            ->assertDontSee('Professionnels')
            ->assertSee("Un trimestre offert au 3è année d'utilisation, puis tous les 2 ans.", false)
            ->assertSee('Accès illimité toute l’année sans paiement pendant les vacances.')
            ->assertSee('70-77-51-58')
            ->assertSee('60-04-26-10')
            ->assertSee('Rapide')
            ->assertSee('Sécurisé')
            ->assertSee('Flexible')
            ->assertSee('Local')
            ->assertSee('Accessible')
            ->assertSee('YELENSCOL au quotidien')
            ->assertSee('Accédez en quelques clics à toutes vos informations.')
            ->assertSee('Vos données scolaires sont au cœur de nos priorités.')
            ->assertSee('Retrouvez votre espace partout et à tout moment.');
    }
}
