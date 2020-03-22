<?php
function isAuthenticated()
{
    return decodeAuthCookie();
}

function requireAuth()
{
    if (!isAuthenticated()) {
        global $session;
        $session->getFlashBag()->add('error', 'Not Authorized');
        redirect('/login.php');
    }
}

function isOwner($userid)
{
    if (!isAuthenticated()) {
        return false;
    }
    return $userid == decodeAuthCookie('auth_user_id');
}

function getAuthenticatedUser()
{
    return findUserById(decodeAuthCookie('auth_user_id'));
}

function setAuthCookie($data, $expTime)
{
  $cookie = new \Symfony\Component\HttpFoundation\Cookie(
    'auth',
    $data,
    $expTime,
    '/',
    'localhost',
    false,
    true
  );
  return $cookie;
}

function decodeAuthCookie($prop = null)
{
  try {
    Firebase\JWT\JWT::$leeway= 1;
    $cookie = Firebase\JWT\JWT::decode(
      request()->cookies->get('auth'),
      getenv("SECRET_KEY"),
      ['HS256']
    );
  } catch (Exception $e){
    return false;
  }
  if ($prop === null) {
      return $cookie;
  }
  if ($prop === 'auth_user_id') {
      $prop = 'sub';
  }
  if (!isset($cookie->$prop)){
    return false;
  }
  return $cookie->$prop;
}

function saveUserData($user)
{
    global $session;

    $session->getFlashBag()->add('success', 'Successfully Logged In');
    $expTime = time() + 3600;
    $jwt = \Firebase\JWT\JWT::encode([
            'iss' => request()->getBaseUrl(),
            'sub' => (int) $user['id'],
            'exp' => $expTime,
            'iat' => time(),
            'nbf' => time()
    ],
        getenv("SECRET_KEY"),
        'HS256'
);
 $cookie = setAuthCookie($jwt, $expTime);
 redirect('/', ['cookie' => $cookie]);
}

