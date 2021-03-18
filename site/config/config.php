<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */
return [
    'debug' => false,
    'languages' => true,
    'api' => [
      'basicAuth' => true,
      'allowInsecure' => true,
      'routes' => [
        [
          'pattern' => '(:any)/main',
          'action'  => function (string $lang) {
            $data = array();

            foreach ($this->site()->children()->listed() as $page) {
              $children = [];

              foreach ($page->children() as $child) {
                if ($child->isListed()) {
                  array_push($children, array(
                      'id' => $child->id(),
                      'title' => $child->content($lang)->title()->value(),
                      'modified' => $child->modified('YmdHi')
                    )
                  );
                }
              }

              array_push($data, array(
                  'id' => $page->id(),
                  'title' => $page->content($lang)->title()->value(),
                  'teasertext' => $page->content($lang)->teasertext()->value(),
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
          'pattern' => '(:any)/meta',
          'action'  => function (string $lang) {
            $data = array();

            foreach ($this->site()->meta()->toPages() as $page) {
              array_push($data, array(
                  'id' => $page->id(),
                  'title' => $page->content($lang)->title()->value(),
                  'modified' => $page->modified('YmdHi')
                )
              );
            };

            return $data;
          }
        ],
        [
          'pattern' => 'updated',
          'action'  => function () {
            $data = array(
                'date' => $this->site()->modified('YmdHi')
            );

            return $data;
          }
        ],
        [
          'pattern' => '(:any)/map/(:any)',
          'action'  => function (string $lang, string $any) {
            $route = $this->site()->find($any);

            $data = array(
              'id' => $route->id(),
              'title' => $route->content($lang)->title()->value(),
              'map' => str_replace('api.', '', $route->file(ltrim($this->site()->find($any)->map()->value(), '- '))->base64()),
              'coords' => array(
                'leftTop' => array(
                  'lat'   => $route->latnw()->value(),
                  'lon'   => $route->lonnw()->value()
                ),
                'rightBot' => array(
                  'lat'   => $route->latse()->value(),
                  'lon'   => $route->lonse()->value()
                )
              ),
              'hasstory' => $route->hasstory()->value(),
              'children' => array()
            );

              foreach ($this->site()->find($any)->children() as $page) {
                $teaserimg;
                $icon;

                if($file = $page->file(ltrim($page->teaserimg()->value(), '- '))) {
                  $teaserimg = $file->crop(500, 357)->base64();
                }

                if($file = $page->file(ltrim($page->icon()->value(), '- '))) {
                  $icon = $file->base64();
                }

                array_push($data['children'], array(
                    'id' => $page->id(),
                    'title' => $page->content($lang)->title()->value(),
                    'teasertext' => $page->content($lang)->teasertext()->value(),
                    'teaserimg' => str_replace('api.', '', $teaserimg),
                    'icon' => str_replace('api.', '', $icon),
                    'coords' => array(
                      'lat'   => $page->lat()->value(),
                      'lon'   => $page->lon()->value()
                    ),
                    'hastime' => $page->hastime()->value(),
                    'time' => $page->content($lang)->time()->value()
                  )
                );
              };

            return $data;
          }
        ],
        [
          'pattern' => '(:any)/article/(:any)',
          'action'  => function (string $lang, string $any) {
                $route = $this->site()->find($any);

                $data = array(
                    'id' => $route->id(),
                    'title' => $route->content($lang)->title()->value(),
                    'teasertext' => $route->content($lang)->teasertext()->value(),
                    'template' => $route->template()->name(),
                    'children' => $route->hasChildren(),
                    'content' => array()
                );

              foreach(json_decode($route->content($lang)->content()) as $item) {

                  switch ($item->type) {
                    case 'img':
                        array_push($data['content'], array(
                            'content' => array(
                                'image' => $route->file($item->content->image[0])->crop(500, 357)->base64(),
                                'alt' => $item->content->alt
                            ),
                            'type' => 'img'
                        ));
                        break;

                    case 'img-slider':

                          $images = array();

                          foreach($item->content->images as $img) {
                              array_push($images, $route->file($img)->crop(500, 357)->base64());
                          }


                        array_push($data['content'], array(
                            'content' => array(
                                'image' => $images,
                            ),
                            'type' => 'img-slider'
                        ));

                        break;

                    case 'audio':
                        array_push($data['content'], array(
                            'content' => array(
                                'audio' => $route->file($item->content->audio[0])->base64(),
                            ),
                            'type' => 'audio'
                        ));
                        break;

                    case 'quotes':
                        $quotes = array();


                        foreach($item->content as $quote) {
                            array_push($quotes, json_decode($quote));
                        }

                        array_push($data['content'], array(
                            'content' => $quotes[0],
                            'type' => 'quotes'
                        ));

                        break;

                    default:
                        array_push($data['content'], $item);
                        break;
                }
              }

              return $data;
          }
        ],
        [
          'pattern' => '(:any)/article/(:any)/(:any)',
          'action'  => function (string $lang, string $any, string $all) {
                $route = $this->site()->find($any)->children()->find($all);

                $data = array(
                    'id' => $route->id(),
                    'title' => $route->content($lang)->title()->value(),
                    'teasertext' => $route->content($lang)->teasertext()->value(),
                    'teaserimg' => $route->file(ltrim($route->teaserimg()->value(), '- '))->crop(500, 357)->base64(),
                    'icon' => $route->file(ltrim($route->icon()->value(), '- '))->base64(),
                    'hastime' => $route->hastime()->value(),
                    'time' => $route->content($lang)->time()->value(),
                    'lat' => $route->lat()->value(),
                    'lon' => $route->lon()->value(),
                    'template' => $route->template()->name(),
                    'content' => array()
                );

              foreach(json_decode($route->content($lang)->content()) as $item) {

                  switch ($item->type) {
                    case 'img':
                        array_push($data['content'], array(
                            'content' => array(
                                'image' => $route->file($item->content->image[0])->crop(500, 357)->base64(),
                                'alt' => $item->content->alt
                            ),
                            'type' => 'img'
                        ));
                        break;

                    case 'img-slider':

                          $images = array();

                          foreach($item->content->images as $img) {
                              array_push($images, $route->file($img)->crop(500, 357)->base64());
                          }


                        array_push($data['content'], array(
                            'content' => array(
                                'image' => $images,
                            ),
                            'type' => 'img-slider'
                        ));

                        break;

                    case 'audio':
                        array_push($data['content'], array(
                            'content' => array(
                                'audio' => $route->file($item->content->audio[0])->base64(),
                            ),
                            'type' => 'audio'
                        ));
                        break;

                    case 'quotes':
                        $quotes = array();


                        foreach($item->content as $quote) {
                            array_push($quotes, json_decode($quote));
                        }

                        array_push($data['content'], array(
                            'content' => $quotes[0],
                            'type' => 'quotes'
                        ));

                        break;

                    default:
                        array_push($data['content'], $item);
                        break;
                }
              }

              return $data;
          }
        ],
        [
          'pattern' => '(:any)/download',
          'action'  => function (string $lang) {
            $data = array(
              'main' => array(),
              'meta' => array()
            );

            foreach ($this->site()->children()->listed() as $page) {
              $children = [];

              foreach ($page->children() as $child) {
                $thischild = array();

                $thischild = array(
                    'modified' => $child->modified('YmdHi'),
                    'id' => $child->id(),
                    'title' => $child->content($lang)->title()->value(),
                    'teasertext' => $child->content($lang)->teasertext()->value(),
                    'teaserimg' => $child->file(ltrim($child->teaserimg()->value(), '- '))->crop(500, 357)->base64(),
                    'icon' => $child->file(ltrim($child->icon()->value(), '- '))->base64(),
                    'hastime' => $child->hastime()->value(),
                    'time' => $child->content($lang)->time()->value(),
                    'coords' => array(
                      'lat' => $child->lat()->value(),
                      'lon' => $child->lon()->value(),
                    ),
                    'template' => $child->template()->name(),
                    'content' => array()
                  );

                foreach(json_decode($child->content($lang)->content()) as $item) {
                    switch ($item->type) {
                      case 'img':
                          array_push($thischild['content'], array(
                              'content' => array(
                                  'image' => $child->file($item->content->image[0])->crop(500, 357)->base64(),
                                  'alt' => $item->content->alt
                              ),
                              'type' => 'img'
                          ));
                          break;

                      case 'img-slider':

                            $images = array();

                            foreach($item->content->images as $img) {
                                array_push($images, $child->file($img)->crop(500, 357)->base64());
                            }


                          array_push($thischild['content'], array(
                              'content' => array(
                                  'image' => $images,
                              ),
                              'type' => 'img-slider'
                          ));

                          break;

                      case 'audio':
                          array_push($thischild['content'], array(
                              'content' => array(
                                  'audio' => $child->file($item->content->audio[0])->base64(),
                              ),
                              'type' => 'audio'
                          ));
                          break;


                      default:
                          array_push($thischild['content'], $item);
                          break;
                  }
                }

                array_push($children, $thischild);

              }


              if ($page->map()->exists()) {
                $topLvPage = array(
                  'id' => $page->id(),
                  'title' => $page->content($lang)->title()->value(),
                  'teasertext' => $page->content($lang)->teasertext()->value(),
                  'map' => $page->map()->toFile()->base64(),
                  'type' => $page->blueprints(),
                  'link' => $page->link(),
                  'status' => $page->status(),
                  'modified' => $page->modified('YmdHi'),
                  'coords' => array(
                    'leftTop' => array(
                      'lat'   => $page->latnw()->value(),
                      'lon'   => $page->lonnw()->value()
                    ),
                    'rightBot' => array(
                      'lat'   => $page->latse()->value(),
                      'lon'   => $page->lonse()->value()
                    )
                  ),
                  'hasstory' => $page->hasstory()->value(),
                  'content' => array(),
                  'children' => $children
                );

  							foreach(json_decode($page->content($lang)->content()) as $item) {

  									switch ($item->type) {
  										case 'img':
  												array_push($topLvPage['content'], array(
  														'content' => array(
  																'image' => $page->file($item->content->image[0])->crop(500, 357)->base64(),
  																'alt' => $item->content->alt
  														),
  														'type' => 'img'
  												));
  												break;

  										case 'img-slider':

  													$images = array();

  													foreach($item->content->images as $img) {
  															array_push($images, $page->file($img)->crop(500, 357)->base64());
  													}


  												array_push($topLvPage['content'], array(
  														'content' => array(
  																'image' => $images,
  														),
  														'type' => 'img-slider'
  												));

  												break;

  										case 'audio':
  												array_push($topLvPage['content'], array(
  														'content' => array(
  																'audio' => $page->file($item->content->audio[0])->base64(),
  														),
  														'type' => 'audio'
  												));
  												break;

  										default:
  												array_push($topLvPage['content'], $item);
  												break;
  								}
  							}
              } else {
                $topLvPage = array(
                  'id' => $page->id(),
                  'title' => $page->content($lang)->title()->value(),
                  'teasertext' => $page->content($lang)->teasertext()->value(),
                  'type' => $page->blueprints(),
                  'link' => $page->link(),
                  'status' => $page->status(),
                  'modified' => $page->modified('YmdHi'),
                  'children' => $children
                );
              }

              array_push($data['main'], $topLvPage);
            };

            foreach ($this->site()->meta()->toPages() as $page) {

              $thischild = array(
                  'id' => $page->id(),
                  'title' => $page->content($lang)->title()->value(),
                  'modified' => $page->modified('YmdHi'),
                  'teasertext' => $page->content($lang)->teasertext()->value(),
                  'content' => array()
              );

              foreach(json_decode($page->content($lang)->content()) as $item) {
                  switch ($item->type) {
                    case 'img':
                        array_push($thischild['content'], array(
                            'content' => array(
                                'image' => $child->file($item->content->image[0])->crop(500, 357)->base64(),
                                'alt' => $item->content->alt
                            ),
                            'type' => 'img'
                        ));
                        break;

                    case 'img-slider':

                          $images = array();

                          foreach($item->content->images as $img) {
                              array_push($images, $child->file($img)->crop(500, 357)->base64());
                          }


                        array_push($thischild['content'], array(
                            'content' => array(
                                'image' => $images,
                            ),
                            'type' => 'img-slider'
                        ));

                        break;

                    case 'audio':
                        array_push($thischild['content'], array(
                            'content' => array(
                                'audio' => $child->file($item->content->audio[0])->base64(),
                            ),
                            'type' => 'audio'
                        ));
                        break;

                    default:
                        array_push($thischild['content'], $item);
                        break;
                }
              }


              array_push($data['meta'], $thischild);
            };

            return $data;
          }
        ]
      ]
    ]
];
