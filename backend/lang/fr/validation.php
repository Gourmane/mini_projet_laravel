<?php
return [
    'required' => 'Le champ :attribute est obligatoire.', 'string' => 'Le champ :attribute doit être un texte.',
    'email' => 'Saisissez une adresse e-mail valide.', 'integer' => 'Le champ :attribute doit être un entier.',
    'numeric' => 'Le champ :attribute doit être un nombre.', 'boolean' => 'La valeur de :attribute est invalide.',
    'exists' => 'La sélection :attribute est introuvable.', 'unique' => 'Cette valeur de :attribute est déjà utilisée.',
    'in' => 'La valeur de :attribute est invalide.', 'prohibited' => 'Le champ :attribute ne peut pas être envoyé.',
    'min' => ['numeric' => 'Le champ :attribute doit être au moins :min.', 'string' => 'Le champ :attribute doit contenir au moins :min caractères.'],
    'max' => ['numeric' => 'Le champ :attribute ne peut pas dépasser :max.', 'string' => 'Le champ :attribute est limité à :max caractères.'],
    'attributes' => ['name' => 'nom', 'quantity' => 'quantité', 'category_id' => 'catégorie', 'unit_price' => 'prix', 'min_stock' => 'seuil minimum', 'password' => 'mot de passe'],
];
