<?php
/**
 * auto generated by tinyorm 2016-03-08 22:37:57
 * @property int $id
 * @property string $name
 */
namespace library\scaffold;
class Author extends \tinyorm\Entity {
    protected $sourceName = 'author';
    protected $pkName = 'id';
    protected $autoUpdatedCols = array ();
    function getDefaults() {
        return [
            'id' => NULL,
            'name' => NULL,
        ];
    }
}
