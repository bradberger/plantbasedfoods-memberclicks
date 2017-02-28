<?php

class MemberClicks
{
    private $orgID;
    private $clientID;
    private $clientSecret;
    private $accessToken;
    private $cache;

    public function __construct($orgID, $clientID, $clientSecret)
    {
        $this->orgID = $orgID;
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->cache = new MemberclicksCache();
    }

    public function auth($scope = 'read')
    {
        list($code, $result) = $this->post('/oauth/v1/token', array(
            'grant_type' => 'client_credentials',
            'scope' => $scope,
        ), $this->clientID, $this->clientSecret);

        if ($code !== 200) {
            return;
        }

        $this->accessToken = $result->access_token;
        return $this->accessToken;
    }

    private function filterProfiles($profiles, $memberType = '')
    {
        // Only show active profiles.
        $profiles = array_filter($profiles, function($member) {
            return $member['member_status'] === 'Active';
        });

        // Now if member type is defined, filter by member type.
        return array_values($memberType ? array_filter($profiles, function($member) use ($memberType) {
            return $member['member_type'] === $memberType;
        }) : $profiles);
    }

    private function formatProfile($profile)
    {
        $obj = array();
        foreach($profile as $k => $v) {
            $k = trim($k, '[]');
            $k = str_replace(' | ', ' ', $k);
            $k = str_replace(' ', '_', $k);
            $k = strtolower($k);
            $obj[$k] = $v;
        }
        $obj['profile_url'] = plugin_dir_url(__FILE__).'avatar.php?profile='.$obj['profile_id'];
        return $obj;
    }

    private function formatProfiles($profiles)
    {
        $result = array();
        foreach($profiles as $p) {
            array_push($result, $this->formatProfile($p));
        }
        return $result;
    }

    public function profiles($memberType = '')
    {
        $profiles = $this->cache->get('profiles');
        if ($profiles) {
            return array(200, $this->filterProfiles($profiles, $memberType));
        }

        // Get first set of profiles to determine number of pages.
        list($code, $result) = $this->get('/api/v1/profile?pageNumber=1');
        if ($code != 200) {
            return array($code, array());
        }

        // After that, then get the rest of the pages if there are more.
        $profiles = $this->formatProfiles($result->profiles);

        if ($result->totalPageCount > 1) {
            $pages = $result->totalPageCount+1;
            for ($i = 2; $i < $pages; $i++) {
                list($code, $result) = $this->get(sprintf('/api/v1/profile?pageNumber=%s', $i));
                if ($code != 200) {
                    return array($code, $profiles);
                }
                // Append the formatted profiles.
                foreach($this->formatProfiles($result->profiles) as $p) {
                    array_push($profiles, $p);
                }
            }
        }

        $this->cache->set('profiles', $profiles);
        return array(200, $this->filterProfiles($profiles, $memberType));
    }

    public function profile($profileID)
    {

        return $this->get(sprintf('/api/v1/profile/%s', $profileID));
    }

    public function get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://%s.memberclicks.net/%s', $this->orgID, ltrim($url, '/')));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->accessToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                sprintf('Authorization: Bearer %s', $this->accessToken),
            ));
        }
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);;
        curl_close($ch);
        return array($code, json_decode($result));
    }

    public function post($url, Array $fields = array(), $username = '', $password = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://%s.memberclicks.net/%s', $this->orgID, ltrim($url, '/')));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $username, $password));
        }
        if ($this->accessToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Authorization: Bearer %s',
            ));
        }
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);;
        curl_close($ch);

        update_option('memberclicks_access_token', $result->accessToken);
        update_option('memberclicks_access_token_expiry', time()+(int)$result->expires_in);
        return array($code, json_decode($result));
    }
}
