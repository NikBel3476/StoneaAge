<?php

class Human extends Animal {
    function __construct($data) {
        parent::__construct($data);
        $this->body = $data->body;
        $this->left_hand = $data->left_hand;
        $this->right_hand = $data->right_hand;
        $this->backpack = $data->backpack;
        $this->protection = $data->protection;
        $this->user_id = $data->user_id;
        $this->direction = $data->direction;
    }

    public function hit($damage = 0) {
        // если нанесен урон, то нанести его с учетом брони и одежды
        if ($damage > 0) {
            // учесть защиту игрока, вычесть часть дамаги из предметов
            if($this->body->protection){
                $damage -= $this->body->protection;
            }
            // нанести оставшуюся дамагу с помощью
            return parent::hit($damage);
        }
        return parent::hit();
    }

    protected function destroy() {
        // Все, что лежит в карманах, вывалить на карту (предметам задать x, y)
        // предмет из левой руки
        // НУЖНО ПЕРЕДЕЛАТЬ
        /*if ($this->left_hand) {
            $this->left_hand->x = $this->x;
            $this->left_hand->y = $this->y;
        }
        // предмет из правой руки
        if ($this->right_hand) {
            $this->right_hand->x = $this->x;
            $this->right_hand->y = $this->y;
        }
        // предмет из рюкзака
        if ($this->backpack) {
            $this->backpack->x = $this->x;
            $this->backpack->y = $this->y;
        }
        // надетая на тело одежда
        if ($this->body) {
            $this->body->x = $this->x;
            $this->body->y = $this->y;
        }*/
    }

    private function canMove($x, $y, $tiles, $width, $height, $humans, $direction) {
        $result = [];
        if ($this->satiety > 0) {
            $this->satiety -= 1;
        } else {
            $this->hp -= 1;
        };
        if ($x >= 0 && $y >= 0 && $x <= $width - 1 && $y <= $height - 1) { // проверка на границу карты
            // берем tile, на который хотим пойти
            for ($i = 0; $i < count($tiles); $i++) {
                if ($tiles[$i]->x == $x && $tiles[$i]->y == $y) {
                    $tile = $tiles[$i];
                    break;
                }
            }
            if ((int)$tile->type === 0) {     // проверяем можно ли пройти
                if ($tile->name !== 'water') {
                    if ($this->right_hand->damage) {
                        $tile->hit($this->right_hand->damage);
                        $this->right_hand->hit(1); // уменьшение прочности оружия
                        $result[] = [
                            'type' => 'item',
                            'id' => $this->right_hand->id,
                            'hp' => $this->right_hand->hp
                        ];
                    } else {
                        $tile->hit(1);
                        $this->hit(1);
                    }
                    $result[] = [
                        'type' => 'tile',
                        'id' => $tile->id,
                        'hp' => $tile->hp
                    ];
                } else {
                    $x = $this->x;
                    $y = $this->y;
                }
            } else { // проверяем, нет ли перед нами человека
                foreach ($humans as $val) { // берем этого человека
                    if ((int)$val->x === (int)$x && (int)$val->y === (int)$y) {
                        $human = $val;
                        break;
                    }
                }
                if ($human) {
                    if ($this->right_hand->damage) { // проверяем, есть ли у нас оружие
                        $human->hit($this->right_hand->damage);
                        $this->right_hand->hit(1);
                        $result[] = [
                            'type' => 'item',
                            'id' => $this->right_hand->id,
                            'hp' => $this->right_hand->hp
                        ];
                    } else {
                        $human->hit(5);
                    }
                    $result[] = [
                        'type' => 'human',
                        'id' => $human->id,
                        'hp' => $human->hp
                    ];
                    $x = $this->x;
                    $y = $this->y;
                }
            }
        } else {
            $x = $this->x;
            $y = $this->y;
        }
        $result[] = [
            'type' => 'human',
            'id' => $this->id,
            'x' => $x,
            'y' => $y,
            'hp' => $this->hp,
            'satiety' => $this->satiety,
            'direction' => $direction
        ];
        return $result;
    }

    public function move($map, $direction, $humans) {
        // взять непроходимые предметы на карте
        // выбираем непроходимые объекты на карте
        $tiles = $map['tiles'];
        $width = $map['map']->width;
        $height = $map['map']->height;
        switch ($direction) {
            case 'left':
                $x = $this->x - 1;
                $y = $this->y;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'left');
                break;
            case 'right':
                $x = $this->x + 1;
                $y = $this->y;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'right');
                break;
            case 'up':
                $x = $this->x;
                $y = $this->y - 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'up');
                break;
            case 'down':
                $x = $this->x;
                $y = $this->y + 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'down');
                break;
            case 'leftUp':
                $x = $this->x - 1;
                $y = $this->y - 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'leftUp');
                break;
            case 'rightUp':
                $x = $this->x + 1;
                $y = $this->y - 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'rightUp');
                break;
            case 'leftDown':
                $x = $this->x - 1;
                $y = $this->y + 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'leftDown');
                break;
            case 'rightDown':
                $x = $this->x + 1;
                $y = $this->y + 1;
                return $this->canMove($x, $y, $tiles, $width, $height, $humans, 'rightDown');
                break;
        }
        return false;
    }

    public function takeItem($item) {
        if ($this->right_hand && $this->left_hand && $this->backpack && $this->body) {
            return false;
        }
        if ($this->right_hand && $this->left_hand) {
            $this->backpack = $this->right_hand;
            $this->right_hand = $item;
            return true;
        }
        if ($this->right_hand) {
            $this->left_hand = $item;
            return true;
        }
        $this->right_hand = $item;
        return [
            'right_hand' => $this->right_hand,
            'left_hand' => $this->left_hand,
            'backpack' => $this->backpack
            //'body' => $this->body
        ];
    }

    public function putOn() {
        // для проверки
        //$this->right_hand = (object) ['type' => 'clothes'];
        if($this->right_hand->type === 'clothes') {    // переделать clothes
            $this->body = $this->right_hand;
            $this->right_hand = null;
            return true;
        } elseif ($this->left_hand->type === 'clothes') {
            $this->body = $this->left_hand;
            $this->left_hand = null;
            return true;
        }
        return false;
    }

    public function putOnBackpack() {
        // для проверки
        if($this->right_hand) {
            return [
                'right_hand' => $this->backpack,
                'backpack' =>  $this->right_hand
            ];
        } elseif ($this->left_hand) {
            return [
                'left_hand' => $this->backpack,
                'backpack' => $this->left_hand
            ];
        } elseif ($this->backpack) {
            return [
                'right_hand' => $this->backpack,
                'backpack' => $this->right_hand
            ];
        }
        return false;
    }

    public function shot() {
        // ???
    }

    public function repair() {
        // для проверки
        //$this->right_hand = (object) ['type' => 'weapon'];
        //$this->left_hand = (object) ['type' => 'resource'];
        if ($this->right_hand->type === 'weapon' && $this->left_hand->type === 'resource') {
            return [
                'itemId' => $this->right_hand,  // возвращаем id предмета и ресурса
                'resourceId' => $this->left_hand
            ];
        }
    }

    public function fix() {

    }

    public function eat() {
        $result = [];
        if ($this->hp < 100 || $this->satiety < 100) {
            if($this->right_hand->type === 'food') {
                if ($this->hp < 100) {
                    $this->hp += $this->right_hand->calories;
                    if ($this->hp > 100) $this->hp = 100;
                } else {
                    $this->satiety += $this->right_hand->calories;
                    if ($this->satiety > 100) $this->satiety = 100;
                }
                $this->right_hand->count--;
                $result[] = [
                    'type' => 'food',
                    'id' => $this->right_hand->id,
                    'count' => $this->right_hand->count
                ];
            } elseif ($this->left_hand->type === 'food') {
                if ($this->hp < 100) {
                    $this->hp += $this->left_hand->calories;
                    if ($this->hp > 100) $this->hp = 100;
                } else {
                    $this->satiety += $this->left_hand->calories;
                    if ($this->satiety > 100) $this->satiety = 100;
                }
                $this->left_hand->count--;
                $result[] = [
                    'type' => 'food',
                    'id' => $this->left_hand->id,
                    'count' => $this->left_hand->count
                ];
            }
            $result[] = [
                'type' => 'human',
                'hp' => $this->hp,
                'satiety' => $this->satiety
            ];
        }
        return $result;
    }

    public function makeItem() {
        $result = [];
        if ($this->right_hand && $this->left_hand) {
            if ($this->right_hand->name === 'stone' && $this->left_hand->name === 'wood') {
                $result[] = [
                    'type' => 'delete',
                    'id' => $this->right_hand->id,
                ];
                $result[] = [
                    'type' => 'delete',
                    'id' => $this->left_hand->id,
                ];
                $result[] = [
                    'type' => 'create',
                    'type_id' => 1
                ];
            } elseif ($this->right_hand->name === 'wood' && $this->left_hand->name === 'stone') {
                $result[] = [
                    'type' => 'delete',
                    'id' => $this->right_hand->id,
                ];
                $result[] = [
                    'type' => 'delete',
                    'id' => $this->left_hand->id,
                ];
                $result[] = [
                    'type' => 'create',
                    'type_id' => 1
                ];
            }
        }
        return $result;
    }

    public function makeBuilding($userId) {

    }

    public function keepBuilding($userId, $buildingId) {

    }
}
