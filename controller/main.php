<?php
/**
 *
 * phpBB mentions. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, paul999, https://www.phpbbextensions.io
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace paul999\mention\controller;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * phpBB mentions main controller.
 */
class main
{
	/**
     * @var user
     */
	protected $user;

    /**
     * @var driver_interface
     */
    private $db;

    /**
     * @var auth
     */
    private $auth;

    /**
     * Constructor
     *
     * @param user $user
     * @param driver_interface $db
     * @param auth $auth
     */
	public function __construct(user $user, driver_interface $db, auth $auth)
	{
		$this->user = $user;
        $this->db = $db;
        $this->auth = $auth;
    }

	/**
	 * get a list of users matching on a username (Minimal 3 chars)
	 *
	 * @param string $name
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($name) : Response
	{
	    if ($this->user->data['user_id'] == ANONYMOUS || $this->user->data['is_bot'] || !$this->auth->acl_get('u_can_mention'))
        {
            throw new http_exception(401);
        }

        if (strlen($name) < 3)
        {
            return new JsonResponse([]);
        }

        $sql = 'SELECT user_id, username 
                    FROM ' . USERS_TABLE . ' 
                    WHERE username_clean ' . $this->db->sql_like_expression($name . $this->db->get_any_char());
        $result = $this->db->sql_query($sql);
        $return = [];

        while ($row = $this->db->sql_fetchrow($result))
        {
            $return[] = [
                'username'  => $row['username'],
                'userid'    => $row['user_id'],
            ];
        }
        return new JsonResponse($return);
	}
}
