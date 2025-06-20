<?php

// Énumération = limite les valeurs
namespace App\Enum;

// Restriction des choix du type de transaction
enum TypeTransaction: string
{
    case Revenu = 'Revenu';
    case Depense = 'Dépense';
}