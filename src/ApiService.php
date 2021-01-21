<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ApiService extends Command
{
    protected static $defaultName = 'make:apiservice';

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, '文件名称')
             ->addArgument('desc', InputArgument::REQUIRED, '描述')
             ->setDescription('Create Api Controller')
             ->setHelp('Create Api Controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        $fileName = $this->touch($arguments);
        $output->writeln("<info>touch {$fileName} done</info>");
        return Command::SUCCESS;
    }
    protected function touch($arguments) {
        $fileName    = 'library/Service/'.$arguments['fileName'].'.php';
        $desc        = $arguments['desc'];
        $author      = explode('/',ROOT_PATH)[2] ?? '';
        $mail        = "{$author}@epatient.com";
        $createdTime = date('Y-m-d H:i:s l');
        $string = str_replace(
            ['{{TRIAL_VERSION}}','{{fileName}}','{{desc}}','{{author}}','{{mail}}','{{createdTime}}','{{Service}}'],
            [TRIAL_VERSION,      $fileName,     $desc,     $author,      $mail,    $createdTime,      $arguments['fileName']],
            $this->templates(),
        );
        touch(ROOT_PATH.'/'.$fileName) && chmod(ROOT_PATH.'/'.$fileName,0666) && file_put_contents(ROOT_PATH.'/'.$fileName,$string);
        return $fileName;
    }
    protected function templates() {
        $templates = <<< TEMPLATE
<?php
#########################################################################
# File Name: {{fileName}}
# Desc: {{desc}}
# Author: {{author}}
# mail: {{mail}}
# Created Time: {{createdTime}}
# Trial Version: {{TRIAL_VERSION}}
#########################################################################

class {{Service}} {
    /**
     * 详情 version
     * @package  PACKAGE
     * @param string \$id* ID
     * @return json
     *
     */
    public function detail(\$id) {
        \$data = (new \{{Service}}Model)->detail(\$id);
        return \$this->success(\$data);
    }
    /**
     * 列表 version
     * @package  PACKAGE
     * @param int \$params* PARAMS
     * @return json
     *
     */
    public function search(\$params) {
        \$data = (new \{{Service}}Model)->search(\$params);
        return \$data;
    }
    /**
     * 创建 version
     * @package  PACKAGE
     * @param string \$params* PARAMS
     * @return json
     *
     */
    public function create(\$params) {
        \$data = (new \{{Service}}Model)->create(\$params);
        return \$data;
    }
    /**
     * 更新 version
     * @package  PACKAGE
     * @param string \$params* PARAMS
     * @return json
     *
     */
    public function update(\$params) {
        \$data = (new \{{Service}}Model)->update(\$params);
        return \$data;
    }
}
TEMPLATE;
        return $templates;
    }
}