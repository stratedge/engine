<?php
namespace Stratedge\Engine\Console\Traits;

trait ParseTemplate
{
    /**
     * Loads a template from the given path and replaces variables with the
     * given data
     * 
     * @param  string $template_path
     * @param  array  $data
     * @return string
     */
    public function parseTemplate($template_path, array $data = [])
    {
        $formatted_data = [];

        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) !== '$') {
                $formatted_data['$' . $key] = $value;
            } else {
                $formatted_data[$key] = $value;
            }
        }

        $template = file_get_contents($template_path);

        return strtr($template, $formatted_data);
    }
}