<?php
    public function makeCleanUrl( $url ) 
    {
        $this->logger->log("[Utils] make_clean_url() : START url=[$url]");

        // Trim trailing slashes
        $url = trim($url, '/');

        // If Scheme is not included, prepend it
        if ( !preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {
            $url = 'http://' . $url;
        }

        // Parse Url
        $urlParts = parse_url( $url );

        // remove www
        $clean_url = preg_replace('/^www\./', '', $urlParts['host']);

        $this->logger->log('[Utils] make_clean_url() : END');
        return $clean_url;
    }