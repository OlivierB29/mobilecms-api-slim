<?php

declare(strict_types=1);
namespace App\Application\Middleware;

/**
 * @see       https://github.com/tuupola/slim-jwt-auth
 */


use Psr\Http\Message\ServerRequestInterface;
// wtf ???
// Error: Interface 'Psr\Http\Message\RuleInterface' not found
// use Psr\Http\Message\RuleInterface;
// implements RuleInterface

class FilterRule 
{


    /**
     * Stores all the options passed to the rule
     * @var mixed[]
     */
    /*
    [
        "userrole" => "guest",
        "editorpath" => ["/someapi"],
        "adminpath" => ["/admin"],
        "ignore" => ["/auth"]
    ];
    */
    private $options = [];

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(ServerRequestInterface $request): bool
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);

        /* If request path is matches ignore should not authenticate. */
        foreach ((array)$this->options["ignore"] as $ignore) {
            $ignore = rtrim($ignore, "/");
            if (!!preg_match("@^{$ignore}(/.*)?$@", (string) $uri)) {
                return false;
            }
        }



        return true;
    }

    public function isIgnore(ServerRequestInterface $request): bool{
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);

        /* If request path is matches ignore should not authenticate. */
        foreach ((array)$this->options["ignore"] as $ignore) {
            $ignore = rtrim($ignore, "/");
            if (!!preg_match("@^{$ignore}(/.*)?$@", (string) $uri)) {
                return false;
            }
        }
        return true;
    }
    
}
