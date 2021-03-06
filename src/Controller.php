<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Controller extends Command
{
    protected static $defaultName = 'make:controller';

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, '文件名称')
             ->addArgument('desc', InputArgument::REQUIRED, '描述')
             ->setDescription('Create Core Controller')
             ->setHelp('Create Core Controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        $fileName = $this->touch($arguments);
        $output->writeln("<info>touch {$fileName} done</info>");
        return Command::SUCCESS;
    }
    protected function touch($arguments) {
        $fileName    = 'application/controllers/'.$arguments['fileName'].'.php';
        $desc        = $arguments['desc'];
        $author      = explode('/',ROOT_PATH)[2] ?? '';
        $mail        = "{$author}@epatient.com";
        $createdTime = date('Y-m-d H:i:s l');
        $string = str_replace(
            ['{{TRIAL_VERSION}}','{{fileName}}','{{desc}}','{{author}}','{{mail}}','{{createdTime}}','{{Controller}}'],
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

class {{Controller}}Controller extends \Controller\Api {
    /**
     * 详情 VERSION
     * @package PACKAGE
     * @http-method get
     * @param string \$id* ID
     *
     * @return json
     *
     */
    public function detailAction() {
        ['id'=>\$id] = \$this->_required(['id']);
        \$data = (new \Service\{{Controller}})->detail(\$id);
        return \$this->success(\$data);
    }
    /**
     * 列表 VERSION
     * @package PACKAGE
     * @http-method get
     * @param int \$page* PAGE
     * @param int \$page_size* PAGESIZE
     *
     * @return json
     *
     */
    public function searchAction() {
        \$this->_required(['page','page_size']);
        \$params = \$this->getQuery(['name']);
        \$page = \$this->getQuery(['page','page_size']);
        \$data = (new \Service\{{Controller}})->search(\$params,\$page);
        return \$this->success(\$data);
    }
    /**
     * 创建 VERSION
     * @package PACKAGE
     * @http-method post
     * @param string \$name* NAME
     *
     * @return json
     *
     */
    public function createAction() {
        \$params = \$this->_required(['name']);
        \$data = (new \Service\{{Controller}})->create(\$params);
        return \$this->success(\$data);
    }
    /**
     * 更新 VERSION
     * @package PACKAGE
     * @http-method post
     * @param int \$id* ID
     * @param string \$name* NAME
     *
     * @return json
     *
     */
    public function updateAction() {
        \$params = \$this->_required(['id','name']);
        \$data = (new \Service\{{Controller}})->update(\$params);
        return \$this->success(\$data);
    }
}
TEMPLATE;
        return $templates;
    }
}