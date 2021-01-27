<?php
namespace Trial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Model extends Command
{
    protected static $defaultName = 'make:model';

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, '文件名称')
             ->addArgument('tableName', InputArgument::REQUIRED, '表名')
             ->addArgument('desc', InputArgument::REQUIRED, '描述')
             ->setDescription('Create Core Model')
             ->setHelp('Create Core Model');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $tableName = $input->getArgument('tableName');
        $table = new Table($output);
        $config = \Yaf\Registry::get('config');
        if(!isset($config->db->{TRIAL})){
            $output->writeln('<error>没有找到配置</error>');
            return Command::FAILURE;
        }
        $dbConfig = $config->db->{TRIAL};
        $dblink = mysqli_connect($dbConfig->host,$dbConfig->username,$dbConfig->password);
        if(mysqli_connect_errno($dblink)) {
            $output->writeln('<error>连接 MySQL 失败'.mysqli_connect_error().'</error>');
            return Command::FAILURE;
        }
        mysqli_select_db($dblink,TRIAL);
        $result = mysqli_query($dblink,"SHOW FULL COLUMNS FROM {$tableName}");
        $rows = [];
        $primaryKey = '';
        $fillable = [];
        while($column = mysqli_fetch_assoc($result)){
            $rows[] = $column;
            if($column['Key'] == 'PRI'){
                $primaryKey = $column['Field'];
            }else{
                if(in_array($column['Field'],['created_at', 'updated_at', 'deleted_at'])){
                    continue;
                }
                $fillable[] = "'".$column['Field']."'";
            }
        }
        $fields = [];
        if(isset($rows[0])) {
            $fields = $rows[0];
            $table->setHeaders(array_keys($fields))->setRows($rows);
            $table->render();
        }else{
            $output->writeln('<error>没有找到有效数据</error>');
            return Command::FAILURE;
        }
        $fileName = $this->touch($arguments,$primaryKey,$fillable);
        $output->writeln("<info>touch {$fileName} done</info>");
        return Command::SUCCESS;
    }
    protected function touch($arguments,$primaryKey,$fillable) {
        $fileName    = 'application/models/'.$arguments['fileName'].'.php';
        $desc        = $arguments['desc'];
        $author      = explode('/',ROOT_PATH)[2] ?? '';
        $mail        = "{$author}@epatient.com";
        $createdTime = date('Y-m-d H:i:s l');
        $tableName   = $arguments['tableName'];
        $string = str_replace(
            ['{{TRIAL_VERSION}}','{{fileName}}','{{desc}}','{{author}}','{{mail}}','{{createdTime}}','{{model}}','{{primaryKey}}','{{tableName}}','{{fillable}}'],
            [TRIAL_VERSION,      $fileName,     $desc,     $author,      $mail,    $createdTime,      $arguments['fileName'],     $primaryKey,    $tableName,     implode(', ',$fillable)],
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

use Db\Eloquent as Model;

class {{model}}Model extends Model {
    protected \$connection = TRIAL;
    protected \$table      = '{{tableName}}';
    protected \$primaryKey = '{{primaryKey}}';
    protected \$fillable   = [{{fillable}}];
    protected \$hidden     = ['created_at', 'updated_at', 'deleted_at'];
}
TEMPLATE;
        return $templates;
    }
}