<?php
namespace Stratedge\Engine\Console\Traits;

use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

trait FindFile
{
    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @param  integer         $index
     * @return string
     */
    public function findFile(InputInterface $input, OutputInterface $output, $index = 1)
    {
        $index = $index == 1 ? 'Primary' : 'Secondary';
        $found = false;
        $current_path = getcwd() . DIRECTORY_SEPARATOR;

        while ($found != true) {
            system('clear');
            
            $output->writeln("<comment>Select $index node to relate:</comment>");
            $output->writeln("<comment>Currently viewing: $current_path</comment>");

            $files = [];

            $i = 1;

            foreach (scandir($current_path) as $file) {
                if ($file == '.') {
                    continue;
                }

                $info = new SplFileInfo($current_path . $file);

                if (
                    ($info->getExtension() == 'php' || $info->isDir()) && //PHP files and directories only
                    strlen($info->getRealPath()) >= strlen(getcwd()) && //Can't go up from the pwd
                    $info->getBaseName() !== '.git' //Always ignore the .git folder
                ) {
                    $files[$i] = $info->isDir() ?
                        $file :
                        "<comment>$file</comment>";

                    $i++;
                }
            }

            $question = new ChoiceQuestion(
                'Please select a class or directory:',
                $files
            );

            //Use strip tags to remove formatting added to file names above
            $choice = strip_tags($this->getHelper('question')->ask($input, $output, $question));

            $current_path = realpath($current_path . $choice);

            $info = new SplFileInfo($current_path);

            if ($info->isFile()) {
                $found = true;
            } else {
                $current_path .= DIRECTORY_SEPARATOR;
            }
        }

        return $current_path;
    }
}