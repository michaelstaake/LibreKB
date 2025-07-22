<?php

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'name';
    
    public function getValue($name)
    {
        $result = $this->findBy('name', $name);
        return $result ? $result['value'] : '';
    }
    
    public function setValue($name, $value)
    {
        $sql = "UPDATE {$this->table} SET value = :value WHERE name = :name";
        return $this->execute($sql, ['value' => $value, 'name' => $name]);
    }
    
    public function getMultiple($names)
    {
        $placeholders = ':' . implode(', :', array_keys($names));
        $sql = "SELECT name, value FROM {$this->table} WHERE name IN ({$placeholders})";
        $results = $this->fetchAll($sql, $names);
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result['name']] = $result['value'];
        }
        
        return $settings;
    }
    
    public function setMultiple($settings)
    {
        foreach ($settings as $name => $value) {
            $this->setValue($name, $value);
        }
    }
}
