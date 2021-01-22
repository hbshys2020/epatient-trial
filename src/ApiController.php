<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ApiController extends Command
{
    protected static $defaultName = 'make:apicontroller';

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

class {{Controller}}Controller extends \Auth\Api {
    /**
     * 详情 VERSION
     * @package PACKAGE
     * @http-method get
     * @param string \$id* ID
     *
     * @return json
     *
     * 字段 | 类型 | 描述
     * ---- | ---- | ----
     * id | int | ID
     *
     */
    public function detailAction() {
        ['id'=>\$id] = \$this->_required(['id']);
        \$data = (new \{{Controller}}Model)->detail(\$id);
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
     * 字段 | 类型 | 描述
     * ---- | ---- | ----
     * data | array | DATA
     *
     */
    public function searchAction() {
        \$this->_required(['page','page_size']);
        \$params = \$this->getQuery(['page','page_size']);
        \$data = (new \{{Controller}}Model)->search(\$params);
        return \$this->success(\TYL\Util::opz(\$data));
    }
    /**
     * 创建 VERSION
     * @package PACKAGE
     * @http-method post
     * @param string \$name* NAME
     *
     * @return json
     *
     * 字段 | 类型 | 描述
     * ---- | ---- | ----
     * id | int | ID
     *
     */
    public function createAction() {
        \$params = \$this->_required(['name']);
        \$data = (new \{{Controller}}Model)->create(\$params);
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
     * 字段 | 类型 | 描述
     * ---- | ---- | ----
     * id | int | ID
     *
     */
    public function updateAction() {
        \$params = \$this->_required(['id','name']);
        \$data = (new \{{Controller}}Model)->update(\$params);
        return \$this->success(\$data);
    }
}
TEMPLATE;
        return $templates;
    }
}