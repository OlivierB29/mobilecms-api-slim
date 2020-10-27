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

class AuthorizeRule
{
    private $userrole = null;

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
                return true;
            }
        }

        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["editorpath"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", (string) $uri)) {
                $result = $this->isPermitted($this->userrole, "editor");
                return $result;
            }
        }
        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["adminpath"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", (string) $uri)) {
                $result = $this->isPermitted($this->userrole, "admin");
                return $result;
            }
        }


        return false;
    }



    /**
     * Control if the current user has access to API.
     *
     * @param string $userRole         object
     * @param string   $requiredRole required role
     *
     * @return true if access is authorized
     */
    private function isPermitted(string $userRole, string $requiredRole): bool
    {
        $result = false;
        if (!empty($userRole) && !empty($requiredRole)) {
            if ($requiredRole === "editor") {
                $result = $this->isPermittedEditor($userRole);
            }

            if ($requiredRole === "admin") {
                $result = $this->isPermittedAdmin($userRole);
            }
        }

        return $result;
    }

    /**
     * Control if the current user has access to an editor API.
     *
     * @param string $userRole object
     *
     * @return true if access is authorized
     */
    private function isPermittedEditor(string $userRole): bool
    {
        $result = false;
        if (!empty($userRole) && !empty("editor") && !empty("admin")) {
            if ($userRole === "editor") {
                $result = true;
            } elseif ($userRole === "admin") {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Control if the current user has access to an admin API.
     *
     * @param string $userRole object
     *
     * @return true if access is authorized
     */
    private function isPermittedAdmin($userRole): bool
    {
        $result = false;
        if (!empty($userRole) && !empty("admin")) {
            if ($userRole === "admin") {
                return true;
            }
        }

        return false;
    }

    public function setUserRole(string $role)
    {
        $this->userrole = $role;
    }
}
