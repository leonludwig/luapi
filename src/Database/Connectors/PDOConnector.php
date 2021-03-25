<?php
/**
 * an abstract class to create a Connector for a databse engine supported by pdo.
 */
abstract class PDOConnector{
    /**
     * an associative array containing the connection parameters
     */
    protected array $dbConfig;
    /**
     * the main pdo object used to connect to the database. will be null if the connection failed.
     */
    public ?PDO $pdo;

    /**
     * creates and initializes the object.
     * @param string $configFilePath the path to the configuration file. try to use an absolute path.
     */
    public function __construct(string $configFilePath)
    {
        $this->dbConfig = $this->loadConfig($configFilePath);
        if($this->createConnection()){
            $this->setPDOAttributes();
        }
    }

    /**
     * loads the config file an tries to json-decode it. will return an empty array on error.
     * @param string $configFilePath the path to the configuration file
     * @return array the configuration data
     */
    private function loadConfig(string $configFilePath):array{
        $fileContent = file_get_contents($configFilePath);
        $jsonContent = json_decode($fileContent);
        if($jsonContent !== null && $jsonContent !== false){
            return $jsonContent;
        }
        return array();
    }

    /**
     * tries to connect to the databse specified in the config file.
     * @return bool whether the connection was established successful.
     */
    abstract protected function createConnection():bool;

    /**
     * sets the PDO attributes, ist automatically called after the connection was successfully established.
     * you should override this function if you extend this class.
     */
    abstract protected function setPDOAttributes():void;
}
?>