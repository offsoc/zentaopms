<?php
declare(strict_types=1);
namespace zin;

class checkbox extends wg
{
    protected static array $defineProps = array(
        'text?: string',
        'checked?: bool',
        'name?: string',
        'primary: bool=true',
        'id?: string',
        'disabled?: bool',
        'type: string="checkbox"',
        'value?: string',
        'typeClass?: string',
        'rootClass?: string',
        'labelClass?: string',
        'title?: string'
    );

    public function onAddChild($child)
    {
        if(is_string($child) && !$this->props->has('text'))
        {
            $this->props->set('text', $child);
            return false;
        }
    }

    protected function buildPrimary()
    {
        list($id, $text, $name, $checked, $disabled, $type, $typeClass, $rootClass, $labelClass, $value, $title) = $this->prop(array('id', 'text', 'name', 'checked', 'disabled', 'type', 'typeClass', 'rootClass', 'labelClass', 'value', 'title'));

        if(empty($typeClass)) $typeClass = $type;
        if(empty($id))        $id        = empty($name) ? $this->gid : ($name . '_' . $value);

        return div
        (
            setClass("$typeClass-primary", $rootClass, array('disabled' => $disabled)),
            h::input
            (
                set::type($type),
                set::id($id),
                set::name($name),
                set::checked($checked),
                set($this->props->skip('text,primary,typeClass,rootClass,id,labelClass'))
            ),
            h::label
            (
                set('for', $id),
                setClass($labelClass),
                !empty($title) ? set('title', $title) : null,
                html($text)
            ),
            $this->children()
        );
    }

    protected function build()
    {
        if($this->prop('primary')) return $this->buildPrimary();
        list($text, $type, $typeClass) = $this->prop(array('text', 'type', 'typeClass'));

        return h::label
        (
            setClass(empty($typeClass) ? $type : $typeClass),
            h::input
            (
                set::type($type),
                set($this->props->skip('text,primary,typeClass'))
            ),
            is_string($text) ? span($text, set::className('text')) : $text,
            $this->children()
        );
    }

    public static function create(string|array $checkedOrProps, ?array $props = null, mixed ...$children): static
    {
        $props = $props ? $props : array();
        if(is_array($checkedOrProps))
        {
            $props = array_merge($checkedOrProps, $props);
        }
        else
        {
            $props['checked'] = $checkedOrProps;
        }
        return new static(set($props), ...$children);
    }
}
