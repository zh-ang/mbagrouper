<?php

class Unit {
    public $name;
    public $dir;
    public $count;
    public function __construct($name, $dir, $count=1) {
        $this->name = $name;
        $this->dir = $dir;
        $this->count = $count;
    }
}


class Group {
    public $members;
    public $count;
    public $dir;

    public function __construct() {
        $this->dir = "NEWS";
        $this->members = array();
        $this->count = 0;
    }

    public function acceptable($dir) {
        return (bool) strpbrk($this->dir, $dir);
    }

    public function accept(Unit $u) {
        $this->members[] = $u;
        $this->count += $u->count;
        $this->dir = join("", array_unique(array_intersect(preg_split("//", $u->dir), preg_split("//", $this->dir))));
    }

}

class Executor {
    protected $_group;
    protected $_m = 5;
    protected $_n;
    protected $_list;
    protected $_a;

    public function __construct($list) {
        $this->_list = array();
        foreach ($list as $info) {
            list($name, $dir, $count) = $info;
            $this->_list[] = new Unit($name, $dir, $count);
        }
    }

    public function run() {
        $this->_a = 0;
        $c = 0;
        foreach ($this->_list as $unit) {
            $c += $unit->count;
        }
        $n = (int) floor($c/$this->_m);
        while ($this->execute($n, $this->_list) == false) {
            $this->_a++;
            if ($this->_a % 1000 == 0) {
                printf(".");
            }
            // var_dump($this);
            // $this->output();
            // sleep(1);
        }
        printf("\n");
        shuffle($this->_group);
    }

    public function output() {
        foreach ($this->_group as $id => $group) {
            printf("%d[%s]:", $id+1, $group->dir);
            foreach ($group->members as $unit) {
                printf(" %s", $unit->name);
            }
            printf("\n");
        }
        printf("Success after %d attempts.\n", $this->_a);
        
    }

    public function outputHTML() {
        echo "<p>因为班长说没有预算，所以我们没有美工，凑活看吧！</p>";
        echo "<table border=1>";
        foreach ($this->_group as $id => $group) {
            echo "<tr>";
            echo "<th>第",($id+1),"学习小组[{$group->dir}]</th>";
            foreach ($group->members as $unit) {
                echo "<td>{$unit->name}</td>";
            }
            echo "</tr>";
        }
	echo "</table>";
        // echo "<p>在尝试过{$this->_a}次后，终于分组成功！</p>";
        
    }

    public function execute($n, $list) {
        $this->_n = $n;
        $this->_group = array();
        for ($i=0; $i<$this->_n; $i++) {
            $this->_group[$i] = new Group;
        }
        foreach ($list as $unit) {
            if ($this->insert($unit) == false) {
                return false;
            }
        }
        return true;
    }

    public function insert(Unit $unit) {
        $off = mt_rand() % $this->_n;
        $min = $this->_m;
        for ($i=0; $i<$this->_n; $i++) {
            $k = ($i+$off) % $this->_n;
            if ($this->_group[$k]->acceptable($unit->dir) && $this->_group[$k]->count < $this->_m) {
                $this->_group[$k]->accept($unit);
                return true;
            }
            if ($min > $this->_group[$k]->count) {
                $min = $this->_group[$k]->count;
            }
        }
        if ($min < $this->_m) {
            return false;
        }
        for ($i=0; $i<$this->_n; $i++) {
            $k = ($i+$off) % $this->_n;
            if ($this->_group[$k]->acceptable($unit->dir) && $this->_group[$k]->count == $this->_m) {
                $this->_group[$k]->accept($unit);
                return true;
            }
        }
        return false;
    }

}

switch (php_sapi_name()) {
    case "cli": mt_srand(1); break;
    case "apache2handler": mt_srand($_GET["i"]); break;
}
$data = require("data.php");
$e = new Executor($data);
$e->run();
switch (php_sapi_name()) {
    case "cli": $e->output(); break;
    case "apache2handler": $e->outputHTML(); break;
    default: $e->output(); break;
}


