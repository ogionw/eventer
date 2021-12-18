1)add
`{
    "type": "path",
    "url": "packages/lion/auth",
    "options": {
        "symlink": true
    }
}`
and
`"lion/auth": ""`
to composer json

2)run 
`composer update lion/auth --ignore-platform-reqs`

3)create lion-auth-config.yaml in root folder of he project
4)fill it with
_`params:
secret_value: \{from app in Azure\}
application_id: {from app in Azure}
tenant_id: {from app in Azure}
secret_id: {from app in Azure}
object_id: {from app in Azure}

urls:
process_url_part: {endpoint where auh verification and processing happens}
redirect_url_part: {to where to redirect back from login.microsoft.com after sign-in}`_

5)create at least one controller endpoint that will serve for above 2 purposes.
6)inject the AdServiceFactory and use it to create AdService object
6)When you are redirected back with "code" param, you will be able to use service like this:
`$userData = $this->adService->getUserData({CODE});`
