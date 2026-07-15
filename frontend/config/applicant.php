<?php

$disabilityCategories = [
    'Physical Disability' => [
        'Physical Disability',
        'Lower Limb Amputation/Deformity and Wheelchair Users',
        'Upper Limb Amputation/Deformity',
        'Dwarfism',
        'Epilepsy',
        'Cancer Survivors and Rare Disease Individuals',
        'Chronic Kidney Disease / Dialysis Patient',
        'Diabetes with Complications',
        'Severe Heart Disease',
    ],
    'Hearing Disability' => [
        'Deaf and Hard of Hearing Individuals',
    ],
    'Visual Disability' => [
        'Visually Impaired',
    ],
    'Speech and Language Disability' => [
        'Speech Impairment',
    ],
    'Psychosocial Disability' => [
        'Mental and Psychosocial Disability Individuals',
    ],
    'Intellectual Disability' => [
        'Intellectual Disability',
    ],
    'Learning Disability' => [
        'Learning Disability',
        'Learning Disability (Dyslexic)',
    ],
    'Multiple Disabilities' => [
        'Multiple Disabilities',
    ],
];

return [
    // The first dropdown is the general disability category. The second one
    // is limited to the specific categories under the selected general type.
    'disabilities' => array_keys($disabilityCategories),
    'disability_categories' => $disabilityCategories,
];
