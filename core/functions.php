<?php
if (!function_exists('debug')) {

    function debug($message = NULL)
    {
        if (!empty($message)) {
            echo "<pre>";
            print_r($message);
            echo "</pre>";
            die;
        } else {
            var_dump(NULL);
            die;
        }
    }
}

if (!function_exists('getConfig')) {

    function &getConfig($key = NULL)
    {
        $null = NULL;
        if ($key) {
            if (isset($GLOBALS['config'][$key])) {
                return $GLOBALS['config'][$key];
            } else {
                return $null;
            }
        }
        return $GLOBALS['config'];
    }
}

if (!function_exists('uriDecoder')) {
    function uriDecoder()
    {
        $result = NULL;
        $sn_ = basename($_SERVER['SCRIPT_NAME']);
        $url_ = $_SERVER['PHP_SELF'];

        if (file_exists(APPPATH . '/routes/web.php')) {
            require APPPATH . '/routes/web.php';
        }
        if (file_exists(APPPATH . '/routes/api.php')) {
            require APPPATH . '/routes/api.php';
        }

        if (isset($routes)) {
            foreach ($routes as $key => $a) {

                $pattern = str_replace(['(:num)', '(:alpha)', '(:any)'], ['[0-9]+', '[a-zA-Z]+', '[0-9a-zA-Z\-]+'], trim($key, '/'));
                $pattern = ltrim($pattern, '/');
                $pattern = (!empty($pattern)) ? $pattern : NULL;

                $em_c = trim(explode($sn_, $url_)[1], '/');
                $data = preg_match('#^' . $pattern . '$#', $em_c, $y);

                $pattern = str_replace('(:default)', '/', $pattern);
                $pattern = ltrim($pattern, '/');

                if (!empty($y[0]) || (empty($em_c) && !$pattern)) {

                    $u_ = $em_c;
                    $tu_ = [];
                    $tr_ = NULL;
                    $u_ = explode('/', $u_);
                    $r_ = explode('/', trim($key, '/'));
                    $a_ = explode('/', trim($a, '/'));

                    if ($r_) {
                        foreach ($r_ as $k_ => $r) {

                            if ($r == '(:num)' || $r == '(:alpha)' || $r == '(:any)') {
                                $tr_[] = $u_[$k_];
                            }
                        }
                    }

                    if ($a_) {
                        $x = 0;
                        foreach ($a_ as $ax) {
                            if (strchr($ax, '$')) {
                                $tu_[] = $tr_[$x];
                                $x++;
                            } else {
                                $tu_[] = $ax;
                            }
                        }
                    }

                    $url_ = $sn_ . '/' . implode('/', $tu_);
                }
            }
        }

        $rs_ = explode($sn_, $url_)[1];
        $rs_ = explode('/', $rs_);
        $d_ = NULL;
        $c_ = NULL;
        $cpath_ = NULL;
        $m_ = NULL;
        $p_ = NULL;
        $x_ = 0;
        $df_ = NULL;

        foreach ($rs_ as $rs) {
            if ($rs) {
                $d_ .= '/' . $rs;

                if (!is_dir(APPPATH  . '/' . $d_)) {
                    if ($x_ === 0) {

                        $cpath_ = APPPATH . $d_ . '.php';

                        $df_ = str_replace($rs, '', $d_);
                        $c_ = ucwords($rs);
                    } else {
                        $p_[] = $rs;
                    }
                    $x_++;
                }
            }
        }

        if (!$c_) {
            $cpath_ = APPPATH . ((rtrim($df_, '/')) ? rtrim($df_, '/') : '') . '/home.php';
            $c_ = 'Home';
            $m_ = 'index';
        }

        if (!$m_) {
            $m_ = 'index';
        }

        $result = array(
            'script_path' => $cpath_,
            'directory' => (rtrim($df_, '/')) ? rtrim($df_, '/') : '/',
            'page' => strtolower($c_),
            //'method' => $m_,
            'params' => $p_,
        );

        return $result;
    }
}

if (!function_exists('uriDecoder')) {
    function pagination($limit, $total, $page_number = NULL, array $config = array())
    {
        $page_number = (empty(trim($page_number)) || $page_number == NULL) ? 1 : $page_number;
        $total_pages = ceil($total / $limit);
        $config['link_limit'] = (isset($config['link_limit'])) ? $config['link_limit'] : 2;
        $config['link_limit'] = ($config['link_limit'] * 2) + 1;
        $pagLink = "";
        $page_number = (int) $page_number;
        $link_arr = array();
        if (isset($config['link_limit'])) {

            $link_arr['first'] = 'first';
            $link_arr['prev'] = '<';
            $i = 1;
            if ($page_number != 1) {
                $i = $page_number;
                $last_link = $config['link_limit'] + $page_number + 1;
            } else {
                $last_link = $config['link_limit'] + 1;
            }

            if ($total_pages == $page_number) {
                $link_arr[$total_pages] = (string) $total_pages; //
            } else {
                for ($i; $i < $last_link; $i++) {
                    if ($total_pages >= $i) {
                        $link_arr[$i] = (string) $i;
                    }
                }
            }


            $link_arr['next'] = '>';
            $link_arr['last'] = 'last';
        }
        $data = $link_arr;
        $last_key = $total_pages;
        $el = "";
        $el .= $config['start_tag'];
        foreach ($data as $key => $value) {
            if ($key == $page_number) {
                $el .=  str_replace(['{value}'], [$value], $config['active_link']);
            } else if ($key == 'prev') {
                $el .=  str_replace(['{link}', '{value}'], [((1 >= $page_number) ? $page_number : ($page_number - 1)), $value], $config['link']);
            } else if ($key == 'next') {
                $el .=  str_replace(['{link}', '{value}'], [((!($last_key > $page_number)) ? $page_number : ($page_number + 1)), $value], $config['link']);
            } else if ($key == 'first') {
                $el .=  str_replace(['{link}', '{value}'], [1, 'first'], $config['link']);
            } else if ($key == 'last') {
                $el .=  str_replace(['{link}', '{value}'], [$last_key, 'last'], $config['link']);
            } else {
                $el .=  str_replace(['{link}', '{value}'], [trim($key), $value], $config['link']);
            }
        };
        $el .= $config['end_tag'];
        echo $el;
    }
}

if (!function_exists('escape_string')) {
    function escape_string($value)
    {
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }
}

if (!function_exists('view')) {
    function view($page, $data = NULL, $return = FALSE)
    {
        $page = str_replace('.php', '', $page);
        if (file_exists('pages/' . $page . '.php')) {
            if ($data) {
                extract($data);
            }

            if ($return === TRUE) {
                ob_start();
                include('pages/' . $page . '.php');
                return ob_get_clean();
            } else {
                include('pages/' . $page . '.php');
            }
        } else {
            die("Error while loading page - $page");
        }
    }
}

if (!function_exists('urlSegment')) {
    function urlSegment()
    {
        return uriDecoder()['params'];
    }
}

if (!function_exists('loadPage')) {
    function loadPage($page_path)
    {
        if (php_sapi_name() !== "cli") {
            require_once trim($page_path);
        } else {
            require_once str_replace('index.php\\', '', trim($page_path));
        }
    }
}

if (!function_exists('method')) {
    function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}

if (!function_exists('get')) {
    function get($key = NULL)
    {
        // if ($key) {
        // 	return (isset($_GET[$key])) ? $_GET[$key] : NULL;
        // }
        // return $_GET;
        return _clean_array($_GET, $key);
    }
}

if (!function_exists('post')) {
    function post($key = NULL)
    {
        return _clean_array($_POST, $key);
    }
}

if (!function_exists('put')) {
    function put($key = NULL)
    {
        if (method() === 'put') {
            parse_str(file_get_contents("php://input"), $request);
            return _clean_array($request, $key);
        } else {
            return NULL;
        }
    }
}

if (!function_exists('_clean_array')) {
    function _clean_array($array, $index = NULL)
    {
        isset($index) or $index = array_keys($array);

        if (is_array($index)) {
            $output = array();
            foreach ($index as $key) {
                $output[$key] = _clean_array($array, $key);
            }

            return $output;
        }

        if (isset($array[$index])) {
            $value = escape_string($array[$index]);
        } elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
            $value = $array;
            for ($i = 0; $i < $count; $i++) {
                $key = trim($matches[0][$i], '[]');
                if ($key === '') {
                    break;
                }

                if (isset($value[$key])) {
                    $value = escape_string($value[$key]);
                } else {
                    return NULL;
                }
            }
        } else {
            return NULL;
        }

        return $value;
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl()
    {
        return $GLOBALS['config']['base_url'];
    }
}
