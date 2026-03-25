<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class StatistiqueController extends Controller
{

    /**
     * Statistiques globales
     */
    public function index()
    {
        $stats = \App\Models\Commande::selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return response()->json([
            'total' => \App\Models\Commande::count(),
            'en_attente' => $stats['en_attente'] ?? 0,
            'confirmee' => $stats['confirmee'] ?? 0,
            'en_cours' => $stats['en_cours'] ?? 0,
            'livree' => $stats['livree'] ?? 0
        ]);
    }

    /**
     * Statistiques par MOIS
     */
    public function statistiquesParMois()
    {
        $commandes = \App\Models\Commande::selectRaw("strftime('%Y-%m', dateCreation) as mois, statut, count(*) as count")
            ->groupBy('mois', 'statut')
            ->get();

        $stats = [];
        foreach ($commandes as $c) {
            $mois = $c->mois;
            if (!isset($stats[$mois])) {
                $stats[$mois] = ['total' => 0, 'livrees' => 0, 'taux_livraison' => 0];
            }
            $stats[$mois]['total'] += $c->count;
            if ($c->statut === 'livree') {
                $stats[$mois]['livrees'] += $c->count;
            }
        }

        foreach ($stats as &$data) {
            $data['taux_livraison'] = $data['total'] > 0 
                ? round(($data['livrees'] / $data['total']) * 100, 2) 
                : 0;
        }

        return response()->json($stats);
    }

    /**
     * Statistiques par SEMAINE
     */
    public function statistiquesParSemaine()
    {
        // Note: strftime('%W', ...) starts from 00. %Y-%W is used for grouping.
        $commandes = \App\Models\Commande::selectRaw("strftime('%Y-W%W', dateCreation) as semaine, statut, count(*) as count")
            ->groupBy('semaine', 'statut')
            ->get();

        $stats = [];
        foreach ($commandes as $c) {
            $semaine = $c->semaine;
            if (!isset($stats[$semaine])) {
                $stats[$semaine] = ['total' => 0, 'livrees' => 0, 'taux_livraison' => 0];
            }
            $stats[$semaine]['total'] += $c->count;
            if ($c->statut === 'livree') {
                $stats[$semaine]['livrees'] += $c->count;
            }
        }

        foreach ($stats as &$data) {
            $data['taux_livraison'] = $data['total'] > 0 
                ? round(($data['livrees'] / $data['total']) * 100, 2) 
                : 0;
        }

        return response()->json($stats);
    }

    /**
     * Statistiques par région (Professionnel)
     */
    public function statistiquesParRegion()
    {
        $commandes = \App\Models\Commande::selectRaw("region, statut, count(*) as count")
            ->groupBy('region', 'statut')
            ->get();

        $stats = [];
        foreach ($commandes as $c) {
            $region = $c->region;
            if (!isset($stats[$region])) {
                $stats[$region] = ['total' => 0, 'livrees' => 0, 'taux_livraison' => 0];
            }
            $stats[$region]['total'] += $c->count;
            if ($c->statut === 'livree') {
                $stats[$region]['livrees'] += $c->count;
            }
        }

        foreach ($stats as &$data) {
            $data['taux_livraison'] = $data['total'] > 0 
                ? round(($data['livrees'] / $data['total']) * 100, 2) 
                : 0;
        }

        return response()->json($stats);
    }

    /**
     * Statistiques par région (Simples)
     */
    public function parRegion()
    {
        $commandes = \App\Models\Commande::selectRaw("region, statut, count(*) as count")
            ->groupBy('region', 'statut')
            ->get();

        $stats = [];
        foreach ($commandes as $c) {
            $reg = $c->region;
            if (!isset($stats[$reg])) {
                $stats[$reg] = [
                    'region' => $reg,
                    'total' => 0,
                    'en_attente' => 0,
                    'confirmee' => 0,
                    'en_cours' => 0,
                    'livree' => 0
                ];
            }
            $stats[$reg]['total'] += $c->count;
            if (isset($stats[$reg][$c->statut])) {
                $stats[$reg][$c->statut] += $c->count;
            }
        }

        return response()->json(array_values($stats));
    }
}
