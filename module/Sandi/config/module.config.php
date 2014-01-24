<?php
return array (
		'controllers' => array (
				'invokables' => array (
						'Sandi\Controller\Sandi' => 'Sandi\Controller\SandiController',
						'Sandi\Controller\User' => 'Sandi\Controller\UserController' 
				) 
		),
		
		'router' => array (
				'routes' => array (
						'sandi' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '/sandi[/][:action[/:id[/:subaction]]]',
										'constraints' => array (
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+',
												// 'subaction' => '[a-zA-Z][a-zA-Z0-9_-]*',
												// 'subaction' => '[a-zA-Z][a-zA-Z0-9_-]*.[a-zA-Z]*',
												'subaction' => '[0-9]+' 
										),
										'defaults' => array (
												'controller' => 'Sandi\Controller\Sandi',
												'action' => 'index' 
										) 
								) 
						),
						
						'user' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '/user[/][:action][/:id]',
										'constraints' => array (
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+' 
										),
										'defaults' => array (
												'controller' => 'Sandi\Controller\User',
												'action' => 'index' 
										) 
								) 
						) 
				) 
		),
		
		'module_config' => array (
				'upload_location' => __DIR__ . '/../data/uploads',
				'download_location' => __DIR__ . '/../data/downloads',
				'avatar_location' => __DIR__ . '/../data/avatar' 
		),
		
		'view_manager' => array (
				'template_path_stack' => array (
						'sandi' => __DIR__ . '/../view' 
				) 
		),
		
		'asset_manager' => array (
				'resolver_configs' => array (
						'paths' => array (
								'public' => __DIR__ . '/../public' 
						) 
				) 
		) 
)
;
