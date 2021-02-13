<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */
return [
    'debug' => true,
    'api' => [
      'basicAuth' => true,
      'allowInsecure' => true,
      'routes' => [
        [
          'pattern' => 'main',
          'action'  => function () {
            $data = array();

            foreach ($this->site()->children()->listed() as $page) {
              $children = [];

              foreach ($page->children() as $child) {
                if ($child->isListed()) {
                  array_push($children, array(
                      'id' => $child->id(),
                      'title' => $child->title()->value(),
                      'modified' => $child->modified('YmdHi')
                    )
                  );
                }
              }

              array_push($data, array(
                  'id' => $page->id(),
                  'title' => $page->title()->value(),
                  'teasertext' => $page->teasertext()->value(),
                  'children' => $children,
                  'type' => $page->blueprints(),
                  'link' => $page->link(),
                  'status' => $page->status(),
                  'modified' => $page->modified('YmdHi')
                )
              );
            };

            return $data;
          }
        ],
        [
          'pattern' => 'meta',
          'action'  => function () {
            $data = array();

            foreach ($this->site()->meta()->toPages() as $page) {
              array_push($data, array(
                  'id' => $page->id(),
                  'title' => $page->title()->value(),
                  'modified' => $page->modified('YmdHi')
                )
              );
            };

            return $data;
          }
        ],
        [
          'pattern' => 'map/(:any)',
          'action'  => function ($any) {
            $data = array(
              'body' => array(
                  'id' => $this->site()->find($any)->id(),
                  'title' => $this->site()->find($any)->title()->value(),
                  'map' => str_replace('api.', '', $this->site()->find($any)->file(ltrim($this->site()->find($any)->map()->value(), '- '))->url()),
                  'coords' => array(
                    'leftTop' => array(
                      'lat'   => $this->site()->find($any)->latnw()->value(),
                      'lon'   => $this->site()->find($any)->lonnw()->value()
                    ),
                    'rightBot' => array(
                      'lat'   => $this->site()->find($any)->latse()->value(),
                      'lon'   => $this->site()->find($any)->lonse()->value()
                    )
                  )

                ),
              'children' => array()
            );

            foreach ($this->site()->find($any)->children() as $page) {
              $teaserimg;
              $icon;

              if($file = $page->file(ltrim($page->teaserimg()->value(), '- '))) {
                $teaserimg = $file->crop(500, 357)->url();
              }

              if($file = $page->file(ltrim($page->icon()->value(), '- '))) {
                $icon = $file->url();
              }

              array_push($data['children'], array(
                  'id' => $page->id(),
                  'title' => $page->title()->value(),
                  'teasertext' => $page->teasertext()->value(),
                  'teaserimg' => str_replace('api.', '', $teaserimg),
                  'icon' => str_replace('api.', '', $icon),
                  'coords' => array(
                    'lat'   => $page->lat()->value(),
                    'lon'   => $page->lon()->value()
                  ),
                  'hastime' => $page->hastime()->value(),
                  'time' => $page->time()->value()
                )
              );
            };

            return $data;
          }
        ]
      ]
    ]
];
