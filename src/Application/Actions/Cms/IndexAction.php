<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;



abstract class IndexAction extends CmsAction
{
    /**
     * Index subpath
     * full path, eg : /var/www/html/public/calendar/index/index.json.
     */
    const INDEX_JSON = '/index/index.json';

    /*
    * reserved id column
    */
    const ID = 'id';

    /*
    */
    const FILE = 'file';
    /**
     * @param LoggerInterface $logger
     * @param UserRepository  $userRepository
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

    }


}
