<?php

/**
 * Memo → Forms routing map.
 *
 * Each memo category maps to one or more registered form slugs (see
 * AppServiceProvider::register()). When a memo of a given category is approved
 * and unlocked, the requester is offered exactly these forms to proceed with.
 *
 * To change which form(s) a category leads to, edit the `forms` array — slugs
 * must match a FormDefinition::slug(). An empty array means "no form"
 * (the memo is pure communication). `null`/unset category = general memo.
 *
 * This is the single source of truth for the mapping; nothing else hard-codes
 * the category → form relationship.
 */
return [

    'categories' => [

        'promotion' => [
            'label' => 'Promotion Memo',
            'forms' => [
                'promotion-senior-members-non-teaching',
                'renewal-of-appointment-academic',
                'renewal-of-appointment-non-academic',
            ],
        ],

        'procurement' => [
            'label' => 'Procurement Memo',
            'forms' => [
                'payment-requisition',
                'purchase-works-authorization',
            ],
        ],

        'leave' => [
            'label' => 'Leave Memo',
            'forms' => [
                'annual-leave-application',
                'casual-leave-application',
                'leave-resumption',
            ],
        ],

        'other' => [
            'label' => 'Other',
            'forms' => [],
        ],

    ],

];
