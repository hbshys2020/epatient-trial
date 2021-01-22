<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Service extends Command
{
    protected static $defaultName = 'make:service';

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

namespace Service;

class {{Service}} {
    /**
     * 详情 VERSION
     * @package PACKAGE
     * @param string \$id* ID
     * @return object
     *
     */
    public function detail(\$id) {
        \$data = (new \{{Service}}Model)->detail(\$id);
        return \$this->success(\$data);
    }
    /**
     * 列表 VERSION
     * @package PACKAGE
     * @param array \$params* 查询参数
     * @param array \$page 分页
     * @param array \$order 排序
     * @param array \$fields 字段
     * @return object
     *
     */
    public function search(\$params, \$page=[], \$order=[['id','DESC']], \$fields=['*']) {
        \$query = \TagModel::select(\$fields);
        \$query->where(function(\$query) use (\$params) {
            foreach(\$params as \$column => \$val){
                if(\$val === '') continue;
                if(in_array(\$column,['name'])){
                    \$query->where(\$column,'like',"%{\$val}%");
                }elseif(is_array(\$val)){
                    \$query->whereIn(\$column,\$val);
                }else{
                    \$query->where(\$column,\$val);
                }
            }
        });
        if(!empty(\$order)){
            foreach(\$order as \$val){
                \$query->orderBy(...\$val);
            }
        }
        \$page = array_filter(\$page);
        if(\$page){
            \$page['page']      = \$page['page']      ?? 1;
            \$page['page_size'] = \$page['page_size'] ?? 10;
            \$data = \$query->paginate(\$page['page_size'], '*', 'page', \$page['page']);
        }else{
            \$data = \$query->get();
        }
        return \$data;
    }
    /**
     * 创建 VERSION
     * @package PACKAGE
     * @param string \$params* PARAMS
     * @return object
     *
     */
    public function create(\$params) {
        \$data = (new \{{Service}}Model)->create(\$params);
        return \$data;
    }
    /**
     * 更新 VERSION
     * @package PACKAGE
     * @param string \$params* PARAMS
     * @return object
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