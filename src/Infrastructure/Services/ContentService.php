<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Rest\Response;
use App\Infrastructure\Utils\JsonUtils;

/**
 * Function used for sorting.
 *
 * @param string $key name
 */
function compareIndex(string $key)
{
    /*
     * compare two object using the $key property
     * @param \stdClass a first object to compare
     * @param \stdClass b second object to compare
     * @return int same as strnatcmp
     */
    return function (\stdClass $a, \stdClass $b) use ($key) {
        $result = 0;
        if (!empty($a) && !empty($b) && !empty($a->{$key}) && !empty($b->{$key})) {
            $result = strnatcmp($a->{$key}, $b->{$key});
        }

        return $result;
    };
}

/**
 * Function used for sorting.
 *
 * @param string $key name
 */
function compareIndexReverse(string $key)
{
    /*
     * compare two object using the $key property
     * @param a first object to compare
     * @param b second object to compare
     * @return int same as strnatcmp
     */
    return function (\stdClass $a, \stdClass $b) use ($key) {
        $result = 0;
        if (!empty($a) && !empty($b) && !empty($a->{$key}) && !empty($b->{$key})) {
            $result = strnatcmp($b->{$key}, $a->{$key});
        }

        return $result;
    };
}

/**
 * Read and save data from JSON files.
 * Future plans : consider http://stackoverflow.com/questions/13899342/can-we-use-json-as-a-database
 * Public methods :
 * - getAll : get all elements
 * - get : return a single element
 * - put : create a new item
 * - post : update an existing item.
 */
class ContentService extends AbstractService
{
    /**
     * Constructor.
     *
     * @param string $databasedir eg : public
     */
    public function __construct(string $databasedir)
    {
        $this->databasedir = $databasedir;
    }

    /**
     * Return a record file path.
     *
     * @param string $type : name of type eg : calendar
     * @param string $id   : unique id of record eg : 1
     *
     * @return string /foobar/calendar/index.json
     */
    private function getItemFileName(string $type, string $id, \stdClass $record): string
    {
        $result = $this->getDatabaseDir().'/'.$type.'/';
        // conf "organizeby": "year"
        $conf = $this->getRecordConf($type);
        if (!empty($conf->getString('organizeby'))) {
            // get year from date field
            $recorddate = $record->{$conf->getString('organizefield')};
            $year = substr($recorddate, 0, 4);
            // date should be mandatory
            if (!empty($year)) {
                $result .= $year.'/';
            }
        }
        $result .= $id.'.json';

        return $result;
    }

    private function getItemFileNameWithoutRecord(string $type, string $id): string
    {
        $result = $this->getDatabaseDir().'/'.$type.'/';

        $result .= $id.'.json';

        return $result;
    }

    /**
     * Return a template index file path.
     *
     * @param string $type : name of type eg : calendar
     *
     * @return string /foobar/calendar/index_template.json
     */
    private function getIndexTemplateFileName(string $type): string
    {
        $this->checkType($type);

        return $this->getDatabaseDir().'/'.$type.'/index/index_template.json';
    }

    /**
     * Return a template index cache path.
     *
     * @param string $type : name of type eg : calendar
     *
     * @return string /foobar/calendar/cache_template.json
     */
    private function getCacheTemplateFileName(string $type): string
    {
        $this->checkType($type);

        return $this->getDatabaseDir().'/'.$type.'/index/cache_template.json';
    }

    public function getMetadataFileName(string $type): string
    {
        $this->checkType($type);

        return $this->getDatabaseDir().'/'.$type.'/index/metadata.json';
    }

    public function getTemplateFileName(string $type): string
    {
        $this->checkType($type);

        return $this->getDatabaseDir().'/'.$type.'/index/new.json';
    }

    /**
     * Get a single record.
     *
     * @param string $type     eg: calendar
     * @param string $keyvalue : id value, eg :1
     */
    public function getRecord(string $type, string $keyvalue): Response
    {
        $response = $this->getDefaultResponse();

        // Read the JSON file
        $file = $this->getItemFileNameWithoutRecord($type, $keyvalue);

        // get one element
        if (file_exists($file)) {
            $response->setResult(JsonUtils::readJsonFile($file));
            $response->setCode(200);
        } else {
            $response->setError(404, 'not found '.$type.'/'.$keyvalue);
        }

        return $response;
    }

    /**
     * Delete a record.
     *
     * @param string $type     eg: calendar
     * @param string $keyvalue : id value, eg :1
     */
    public function deleteRecord(string $type, string $keyvalue)
    {
        $response = $this->getDefaultResponse();

        // Read the JSON file
        $file = $this->getItemFileNameWithoutRecord($type, $keyvalue);

        if (file_exists($file)) {
            unlink($file);

            $response->setCode(200);
        } else {
            // @codeCoverageIgnoreStart
            $response->setError(404, 'not found '.$type.' : '.$keyvalue);
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }

    /**
     * Return a filepath from a single filename, only contained in the public databasedir.
     * Valid path :
     * calendar/1.json , new/foobar.json, index/index.json , ...
     * Invalid path :
     * /var/www/private/somefile.sh.
     *
     * $filename : calendar/1.json , new/foobar.json, index/index.json , ...
     *
     * @param string $filename file
     *
     * @return Response object
     */
    public function getFilePath(string $filename): Response
    {
        $response = $this->getDefaultResponse();

        //
        //forbid upper directory
        //
        if (strpos($filename, '..') !== false) {
            // @codeCoverageIgnoreStart
            throw new \Exception('Invalid path '.$filename);
            // @codeCoverageIgnoreEnd
        }

        $file = $this->getDatabaseDir().'/'.$filename;

        // get one element
        if (file_exists($file)) {
            $response->setResult($file);
            $response->setCode(200);
        } else {
            // @codeCoverageIgnoreStart
            $response->setError(404, 'not found '.$file);
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }

    /**
     * Return a single element, from a JSON array stored in file.
     * $filename : JSON data filename eg: [{"id":"1", "foo":"bar"}, {"id":"2", "foo":"bar2"}]
     * $keyname : primary key inside the file eg : id
     * $keyvalue : eg : 1.
     *
     * @param string $filename: index.json
     * @param string $keyname   : id
     * @param string $keyvalue  : 1
     *
     * @return Response object with a JSON object eg : {"id":"1", "foo":"bar"}
     */
    public function get(string $filename, string $keyname, string $keyvalue): Response
    {
        $response = $this->getDefaultResponse();

        // Read the JSON file
        $file = $this->getDatabaseDir().'/'.$filename;
        $data = JsonUtils::readJsonFile($file);

        // get one element
        /** @phpstan-ignore-next-line */
        if (isset($keyvalue)) {
            // extract element data
            $existingObject = JsonUtils::getByKey($data, $keyname, $keyvalue);
            if (isset($existingObject)) {
                $response->setResult($existingObject);
                $response->setCode(200);
            } else {
                // element not found
                $response->setError(404, 'not found '.$keyname.' : '.$keyvalue);
            }
        } else {
            // return all
            $response->setResult($data);
            $response->setCode(200);
        }

        return $response;
    }

    /**
     * Get all JSON files list of a directory
     * eg: [{"id":"1", "filename": "1.json"}, {"id":"2", "filename": "2.json"}].
     *
     * @param string $type eg: calendar
     *
     * @return Response object with a JSON array
     */
    public function getAllObjects(string $type): Response
    {
        $this->checkType($type);
        $response = $this->getDefaultResponse();

        $thelist = [];

        if ($handle = opendir($this->getDatabaseDir().'/'.$type)) {
            while (false !== ($file = readdir($handle))) {
                $fileObject = json_decode('{}');
                if ($file != '.' && $file != '..' && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                    $fileObject->{'filename'} = $file;
                    $fileObject->{'id'} = str_replace('.json', '', $file);
                    array_push($thelist, $fileObject);
                }
            }
            closedir($handle);
        }

        $response->setResult($thelist);
        $response->setCode(200);

        return $response;
    }

    /**
     * Get all elements from an array, contained in a single file.
     *
     * @param string $filename : JSON data filename eg: [{"id":"1", "foo":"bar"}, {"id":"2", "foo":"bar2"}].
     *
     * @return Response object with a JSON array
     */
    public function getAll(string $filename): Response
    {
        $response = $this->getDefaultResponse();

        // Read the JSON file
        $file = $this->getDatabaseDir().'/'.$filename;
        $data = JsonUtils::readJsonFile($file);
        if (isset($data)) {
            $response->setCode(200);
            $response->setResult($data);
        }

        return $response;
    }

    /**
     * Return an index file path.
     *
     * @param string $type : name of type eg : calendar
     *
     * @return string /foobar/calendar/index.json
     */
    public function getIndexFileName(string $type): string
    {
        $this->checkType($type);

        return $this->getDatabaseDir().'/'.$type.'/index/index.json';
    }

    /**
     * Save a record.
     *
     * @param string    $type    : object type (eg : calendar)
     * @param string    $keyname : primary key inside the file.
     * @param \stdClass $record  : JSON data
     */
    public function post(string $type, string $keyname, \stdClass $record)
    {
        $this->checkParams($type, $keyname);
        $response = $this->getDefaultResponse();

        if (!empty($record) && !empty($record->{$keyname})) {
            $response->setResult($record);

            // detect id
            $id = $record->{$keyname};

            // file name
            $file = $this->getItemFileName($type, $id, $record);

            // write to file
            JsonUtils::writeJsonFile($file, $record);
            unset($record);
            $response->setCode(200);
        } else {
            $response->setError(400, 'Bad object parameters');
        }

        return $response;
    }

    /**
     * Update a record.
     *
     * @param string    $type    : object type (eg : calendar)
     * @param string    $keyname : primary key inside the file.
     * @param \stdClass $record  : JSON data
     */
    public function update(string $type, string $keyname, \stdClass $record): Response
    {
        $response = $this->getDefaultResponse();

        if (!empty($record)) {
            $response->setResult($record);
            // detect id
            $id = $record->{$keyname};
            // file name
            $file = $this->getItemFileName($type, $id, $record);

            $existing = JsonUtils::readJsonFile($file);
            JsonUtils::copy($record, $existing);

            // write to file
            JsonUtils::writeJsonFile($file, $existing);
            unset($record);
            $response->setCode(200);
        } else {
            // @codeCoverageIgnoreStart
            $response->setError(400, 'Bad object parameters');
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }

    /**
     * Add object id to index.
     *
     * @param string $type     : object type (eg : calendar)
     * @param string $keyname  : primary key inside the file.
     * @param string $keyvalue : value
     */
    public function publishById(string $type, string $keyname, string $keyvalue): Response
    {
        //  $this->logger->info('publishById' . $type . ',' . $keyname . ',' . $keyvalue);

        $response = $this->getDefaultResponse();

        // file name eg: index.json
        $file = $this->getIndexFileName($type);
        /*
        Load a template for index.
        eg :
        { "id": "", "date": "",  "activity": "", "title": "" }
         */
        $indexValue = null;
        // create an indexed with cached items
        if (\file_exists($this->getCacheTemplateFileName($type))) {
            $indexValue = JsonUtils::readJsonFile($this->getCacheTemplateFileName($type));
        } else {
            $indexValue = JsonUtils::readJsonFile($this->getIndexTemplateFileName($type));
        }

        // Read the full JSON record
        $recordFile = $this->getDatabaseDir().'/'.$type.'/'.$keyvalue.'.json';

        $record = JsonUtils::readJsonFile($recordFile);

        //copy some fields to index
        JsonUtils::copy($record, $indexValue);

        // get index data
        $data = JsonUtils::readJsonFile($file);
        // put index data
        $data = JsonUtils::put($data, $keyname, $indexValue);
        // sort index

        // issue when using a function. array is copied, not referenced
        // $this->sortIndex($data, $type, $keyname);
        // sorted index by a field, or by id
        $sortby = $keyname;
        $sortAscending = false;
        $cacheSize = -1;
        if ($this->getRecordConf($type) != null) {
            if (!empty($this->getRecordConf($type)->getString('sortby'))) {
                $sortby = $this->getRecordConf($type)->getString('sortby');
            }
            if ('asc' === $this->getRecordConf($type)->getString('sortdirection')) {
                $sortAscending = true;
            }
            $cacheSize = $this->getRecordConf($type)->getInteger('cachesize', 0);
        }

        // sort
        if ($sortAscending) {
            usort($data, compareIndex($sortby));
        } else {
            usort($data, compareIndexReverse($sortby));
        }

        // write to file
        JsonUtils::writeJsonFile($file, $data);
        unset($data);
        unset($record);

        $response->setCode(200);
        // set a timestamp response
        // $tempResponse = $response->getResult();
        // $tempResponse->{'timestamp'} = '' . time();
        // $response->setResult($tempResponse);

        return $response;
    }

    /**
     * Rebuild an index.
     *
     * @param string $type    : object type (eg : calendar)
     * @param string $keyname : primary key inside the file.
     */
    public function rebuildIndex(string $type, string $keyname): Response
    {
        $response = $this->getDefaultResponse();

        $data = [];

        // file name eg: index.json

        $indexFile = $this->getIndexFileName($type);

        /*
        Load a template for index.
        eg :
        { "id": "", "date": "",  "activity": "", "title": "" }
         */

        $indexTemplate = JsonUtils::readJsonFile($this->getIndexTemplateFileName($type));

        $cacheTemplate = null;

        if ($handle = opendir($this->getDatabaseDir().'/'.$type)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                    // Read the full JSON record
                    $filename = $this->getDatabaseDir().'/'.$type.'/'.$file;
                    $record = JsonUtils::readJsonFile($filename);

                    //
                    //copy some fields to index
                    //
                    $indexValue = clone $indexTemplate;

                    JsonUtils::copy($record, $indexValue);
                    unset($record);
                    array_push($data, $indexValue);
                    unset($indexValue);
                }
            }
            closedir($handle);
        }

        // sorted index by a field, or by id
        $sortby = $keyname;
        $sortAscending = false;
        $cacheSize = -1;
        if ($this->getRecordConf($type) != null) {
            if (!empty($this->getRecordConf($type)->getString('sortby'))) {
                $sortby = $this->getRecordConf($type)->getString('sortby');
            }
            if ('asc' === $this->getRecordConf($type)->getString('sortdirection')) {
                $sortAscending = true;
            }
            $cacheSize = $this->getRecordConf($type)->getInteger('cachesize', 0);
        }

        // sort
        if ($sortAscending) {
            usort($data, compareIndex($sortby));
        } else {
            usort($data, compareIndexReverse($sortby));
        }

        // create an indexed with cached items
        if (\file_exists($this->getCacheTemplateFileName($type))) {
            $cacheTemplate = JsonUtils::readJsonFile($this->getCacheTemplateFileName($type));
            $i = 0;
            while ($i < $cacheSize && $i < count($data)) {
                $file = $data[$i]->{$keyname};
                $filename = $this->getDatabaseDir().'/'.$type.'/'.$file.'.json';
                $record = JsonUtils::readJsonFile($filename);
                $cacheValue = clone $cacheTemplate;
                JsonUtils::copy($record, $cacheValue);
                $data[$i] = $cacheValue;
                $i++;
            }
        }

        // write to file
        JsonUtils::writeJsonFile($indexFile, $data);
        unset($data);
        $response->setCode(200);

        return $response;
    }

    /**
     * Sort an array, using a configuration.
     *
     * @param array  $data    : index content
     * @param string $type    : object type (eg : calendar)
     * @param string $keyname : default sort key, if conf is empty
     */
    public function sortIndex(array $data, string $type, string $keyname)
    {
        // sorted index by a field, or by id
        $sortby = $keyname;
        $sortAscending = false;
        $cacheSize = -1;
        if ($this->getRecordConf($type) != null) {
            if (!empty($this->getRecordConf($type)->getString('sortby'))) {
                $sortby = $this->getRecordConf($type)->getString('sortby');
            }
            if ('asc' === $this->getRecordConf($type)->getString('sortdirection')) {
                $sortAscending = true;
            }
            $cacheSize = $this->getRecordConf($type)->getInteger('cachesize', 0);
        }

        // sort
        if ($sortAscending) {
            usort($data, compareIndex($sortby));
        } else {
            usort($data, compareIndexReverse($sortby));
        }
    }

    /**
     * Options files content.
     *
     * @param string $filename file
     *
     * @return string options value
     */
    public function options(string $filename)
    {
        $file = $this->getDatabaseDir().'/'.$filename;

        return JsonUtils::readJsonFile($file);
    }

    public function adminOptions(string $filename)
    {
        $file = $this->getDatabaseDir().'/'.$filename;
        //  $tmp = json_decode('{}');
        //  $tmp->{'list'} = JsonUtils::readJsonFile($file);
        $tmp = JsonUtils::readJsonFile($file);

        return $tmp;
    }

    public function deleteRecords(string $type, array $ids)
    {
        $response = $this->getDefaultResponse();

        foreach ($ids as $id) {
            // Read the JSON file
            $file = $this->getDatabaseDir().'/'.$type.'/'.$id.'.json';

            if (file_exists($file)) {
                unlink($file);

                $response->setCode(200);
            } else {
                // @codeCoverageIgnoreStart
                $response->setError(404, 'not found '.$type.' : '.$id);
                // @codeCoverageIgnoreEnd
            }
        }

        return $response;
    }
}
