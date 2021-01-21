<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ApiModel extends Command
{
    protected static $defaultName = 'make:apimodel';

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, '文件名称')
             ->addArgument('desc', InputArgument::REQUIRED, '描述')
             ->setDescription('Create Api Model')
             ->setHelp('Create Api Model');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        $fileName = $this->touch($arguments);
        $output->writeln("<info>touch {$fileName} done</info>");
        return Command::SUCCESS;
    }
    protected function touch($arguments) {
        $fileName    = 'application/models/'.$arguments['fileName'].'.php';
        $desc        = $arguments['desc'];
        $author      = explode('/',ROOT_PATH)[2] ?? '';
        $mail        = "{$author}@epatient.com";
        $createdTime = date('Y-m-d H:i:s l');
        $string = str_replace(
            ['{{TRIAL_VERSION}}','{{fileName}}','{{desc}}','{{author}}','{{mail}}','{{createdTime}}','{{model}}'],
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

class {{model}}Model extends BaseModel {
    // 详情
    public function detail(\$id) {
        \$uri = '/{{model}}/detail';
        return \$this->setHost('core')->get(\$uri, ['id'=>\$id]);
    }
    // 列表
    public function search(\$params) {
        \$uri = '/{{model}}/search';
        return \$this->setHost('core')->get(\$uri, \$params);
    }
    // 创建
    public function create(\$params) {
        \$uri = '/{{model}}/create';
        return \$this->setHost('core')->post(\$uri, \$params);
    }
    // 更新
    public function update(\$params) {
        \$uri = '/{{model}}/update';
        return \$this->setHost('core')->post(\$uri, \$params);
    }
}
TEMPLATE;
        return $templates;
    }
}