<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\Collection;

class MongoStatsService
{
    private Collection $collection;

    public function __construct(string $mongoUri, string $mongoDb)
    {
        $client = new Client($mongoUri);
        $database = $client->selectDatabase($mongoDb);
        $this->collection = $database->selectCollection('commande_stats');
    }

    public function saveCommandeStat(array $data): void
    {
        $this->collection->insertOne($data);
    }

    public function updateCommandeStat(string $commandeNumero, array $data): void
        {
            $this->collection->updateOne(
                ['commande_numero' => $commandeNumero],
                ['$set' => $data]
            );
        }

        public function getStats(
            ?int $menuId = null,
            ?string $dateDebut = null,
            ?string $dateFin = null,
            ?string $statutFilter = 'hors_annulees'
        ): array {
            $match = [];

            if ($statutFilter === 'hors_annulees') {
                $match['statut'] = ['$ne' => 'annulee'];
            } elseif ($statutFilter === 'annulees') {
                $match['statut'] = 'annulee';
            } elseif ($statutFilter === 'toutes') {
                // aucun filtre statut
            } elseif ($statutFilter !== null && $statutFilter !== '') {
                $match['statut'] = $statutFilter;
            }

            if ($menuId !== null) {
                $match['menu_id'] = $menuId;
            }

            if ($dateDebut !== null || $dateFin !== null) {
                $match['date_commande'] = [];

                if ($dateDebut !== null) {
                    $match['date_commande']['$gte'] = $dateDebut;
                }

                if ($dateFin !== null) {
                    $match['date_commande']['$lte'] = $dateFin;
                }
            }

            $pipeline = [];

            if (!empty($match)) {
                $pipeline[] = ['$match' => $match];
            }

            $pipeline[] = [
                '$group' => [
                    '_id' => [
                        'menu_id' => '$menu_id',
                        'menu_titre' => '$menu_titre',
                    ],
                    'nombre_commandes' => ['$sum' => 1],
                    'chiffre_affaire_total' => ['$sum' => '$chiffre_affaire'],
                ]
            ];

            $pipeline[] = [
                '$sort' => ['_id.menu_titre' => 1]
            ];

            $results = $this->collection->aggregate($pipeline)->toArray();

            $formatted = [];

            foreach ($results as $row) {
                $formatted[] = [
                    'menu_id' => $row['_id']['menu_id'],
                    'menu_titre' => $row['_id']['menu_titre'],
                    'nombre_commandes' => $row['nombre_commandes'],
                    'chiffre_affaire_total' => round((float) $row['chiffre_affaire_total'], 2),
                ];
            }

            return $formatted;
        }

        public function getEvolutionStats(
            ?string $dateDebut = null,
            ?string $dateFin = null,
            ?string $statutFilter = 'hors_annulees'
        ): array {
            $match = [];

            if ($statutFilter === 'hors_annulees') {
                $match['statut'] = ['$ne' => 'annulee'];
            } elseif ($statutFilter === 'annulees') {
                $match['statut'] = 'annulee';
            } elseif ($statutFilter === 'toutes') {
                // aucun filtre statut
            } elseif ($statutFilter !== null && $statutFilter !== '') {
                $match['statut'] = $statutFilter;
            }

            if ($dateDebut !== null || $dateFin !== null) {
                $match['date_commande'] = [];

                if ($dateDebut !== null) {
                    $match['date_commande']['$gte'] = $dateDebut;
                }

                if ($dateFin !== null) {
                    $match['date_commande']['$lte'] = $dateFin;
                }
            }

            $pipeline = [];

            if (!empty($match)) {
                $pipeline[] = ['$match' => $match];
            }

            $pipeline[] = [
                '$group' => [
                    '_id' => '$date_commande',
                    'nombre_commandes' => ['$sum' => 1],
                    'chiffre_affaire_total' => ['$sum' => '$chiffre_affaire'],
                ]
            ];

            $pipeline[] = [
                '$sort' => ['_id' => 1]
            ];

            $results = $this->collection->aggregate($pipeline)->toArray();

            $formatted = [];

            foreach ($results as $row) {
                $formatted[] = [
                    'date_commande' => $row['_id'],
                    'nombre_commandes' => $row['nombre_commandes'],
                    'chiffre_affaire_total' => round((float) $row['chiffre_affaire_total'], 2),
                ];
            }

            return $formatted;
        }
}