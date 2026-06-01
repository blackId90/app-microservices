<?php

return [

    'exclude' => [

        /*
        |--------------------------------------------------------------------------
        | Exclude Route Name
        |--------------------------------------------------------------------------
        |
        | This value is the route name of your application's route, which will be excluded
        | from the HasAccess middleware.
        |
        */

        'route_name' => [
            // add another route name here
        ],

        /*
        |--------------------------------------------------------------------------
        | Exclude from Header X-Request-Source
        |--------------------------------------------------------------------------
        |
        | This value is the header X-Request-Source of your application's route, which will be excluded
        | from the HasAccess middleware.
        |
        */

        'header' => [
            'AuthService',
            // add another header here
        ],

    ],

    'action' => [

        /*
        |--------------------------------------------------------------------------
        | Valid Parameter List Data (Browse)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = Deleted Data.
        | 3 = All Data.
        |
        */

        'browse' => [
            'type' => 'type_list',
			'value' => ['1', '2', '3']
        ],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Create Data (Create)
        |--------------------------------------------------------------------------
        |
        | 1 = Default Value.
        |
        */

        'create' => [
			'type' => 'type_created',
			'value' => ['1']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Read Data (Read)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = All Data.
        |
        */

		'read' => [
			'type' => 'type_detail',
			'value' => ['1', '2']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Update Data (Edit)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = All Data.
        |
        */

		'update' => [
			'type' => 'type_updated',
			'value' => ['1', '2']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Delete Data (Delete)
        |--------------------------------------------------------------------------
        |
        | 1 = Soft Deleted.
        | 2 = Restore from Trash.
        | 3 = Deleted from Trash.
        | 4 = Permanent Deleted.
        |
        */

		'delete' => [
			'type' => 'type_deleted',
			'value' => ['1', '2', '3', '4']
		],

    ]

];
