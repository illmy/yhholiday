<?php
/**
 * 判断日期是否为节假日
 * 
 * 
 * @author illmy <335111164@qq.com>
 */
namespace illmy\Yhholiday;
use illmy\Yhholiday\Support\Config;
use illmy\Yhholiday\Traits\HasHttpRequest;

class Holiday
{
    use HasHttpRequest;

    protected $config;

    protected $defaultWay;

    protected $holiday = [];

    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
        if (!empty($config['default'])) {
            $this->setDefaultWay($config['default']);
        }
    }

    public function isHoliday(array $date,$format = 'd')
    {
        $data = [];
        foreach ($date as $key => $value) {
            if (!$tm = strtotime($value)) {
                $data[$value] = '-1';
                continue;
            }
            $y = substr($value,0,4);
            $db = $this->getHolidayData($y);
            $data[$value] = $this->parseFormat($this->holiday[$y],$value,$format);
        }
        return $data;
    }

    protected function parseFormat($db = [],$dt = '',$format = 'd')
    {
        switch ($format) {
            case 'd':
                $dt = strtotime($dt);
                $m = date('Ym',$dt);
                $d = (string)date('d',$dt);
                if (!isset($db[$m][$d])) {
                    return '0';
                }
                return $db[$m][$d];
                break;
            case 'm':
                $dt = strtotime($dt.'01');
                $m = date('Ym',$dt);
                return $db[$m];
                break;
            case 'y':
                return $db;
                break;
            default:
                return '-1';
                break;
        }
    }

    public function getHolidayData($y)
    {
        $db = $this->formatDb($this->config->get('default','local'));
        return $data = $this->$db($y);
            
    }

    public function formatDb($db)
    {
        $db = ucfirst(str_replace(['-','_',''],'',$db));
        return 'get'.$db.'Db';
    }

    public function getLocalDb($y) 
    {
        if (!is_numeric($y)) {
            return false;
        }
        if ($y < 2000 || $y > date('Y')) {
            return false;
        }
        if (isset($this->holiday[$y])) {
            return true;
        }
        $name = str_replace('\\',DIRECTORY_SEPARATOR,__DIR__.'/Data/data_'.$y.'.php');
        if (!file_exists($name)) {
            $this->getRemoteY($y,$name);
            //return true;
        }
        $data = require_once $name;
        $this->holiday[$y] = $data;
        return true;
    }

    public function getRemoteDb($param = [])
    {
        $url = "http://www.easybots.cn/api/holiday.php";
        $data = $this->get($url,$param);
        return json_decode($data,true);
    }

    public function getRemoteY($y,$name)
    {
        
        $m = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $data = [];
        foreach ($m as  $value) {
            $data[$y.$value] = $this->getRemoteDb(['m' => $y.$value])[$y.$value];
        }

        //保证目录可写
        file_put_contents($name,'<?php'.PHP_EOL.'return '.var_export($data,true).';');
    }

    /**
     * 设置默认方式
     *
     * @param [type] $name
     * @return void
     */
    public function setDefaultWay($name)
    {
        $this->defaultWay = $name;

        return $this;
    }
}