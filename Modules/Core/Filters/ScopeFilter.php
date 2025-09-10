<?php
namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ScopeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $required = $arguments ?? [];
        $json = $request->getHeaderLine('X-ApiKey-Scopes');
        $scopes = $json ? json_decode($json, true) : [];

        foreach ($required as $scope) {
            if (!in_array($scope, $scopes ?? [], true)) {
                return service('response')->setStatusCode(403)->setJSON([
                    'error' => 'insufficient_scope', 'required' => $required
                ]);
            }
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
