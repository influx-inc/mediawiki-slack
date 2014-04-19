<?php
/**
 * Mediawiki Slack integration extension.
 * @version 0.1.0
 *
 * Copyright (C) 2014 George Goldberg <george@grundleborg.com>
 * @author George Goldberg <george@grundleborg.com>
 *
 * @license MIT
 *
 * @file The hooks of the Mediawiki Slack integration extension.
 *       For more information on Slack, see http://www.slack.com.
 * @ingroup Extensions
 */

// Parts of the Slack Incoming WebHooks Integration URL
define('SLACK_URL_PREFIX', 'https://');
define('SLACK_URL_SUFFIX', '.slack.com/services/hooks/incoming-webhook?token=');

class SlackHooks {

  public static function encodeSlackChars($in) {
    // This function encodes chars that the Slack API expects to be encoded in the JSON values.
    // See https://api.slack.com/docs/formatting for details.
    $o = str_replace("&", "&amp;", $in);
    $o = str_replace("<", "&lt;", $o);
    $o = str_replace(">", "&gt;", $o);

    return $o;
  }

  public static function onPageContentSaveComplete( $article, $user, $content, $summary, $isMinor, 
    $isWatch, $section, $flags, $revision, $status, $baseRevId ) {
      global $wgSlackTeamName, $wgSlackIntegrationToken, $wgSlackChannel, $wgSlackUserName;

      // Build the Slack Incoming WebHooks URL.
      $url = SLACK_URL_PREFIX.$wgSlackTeamName.SLACK_URL_SUFFIX.$wgSlackIntegrationToken;

      wfDebug("Slack URL: ".$url."\n");

      // Build the message we're going to post to Slack.
      $message = '*<'.SlackHooks::encodeSlackChars($article->getTitle()->getFullURL())
                     .'|'.SlackHooks::encodeSlackChars($article->getTitle()).'>* '
                .'modified by *'.SlackHooks::encodeSlackChars($user->getName()).'*: '
                .SlackHooks::encodeSlackChars($summary).'.';

      // Build the WebHook Payload.
      $post = "payload="
        .urlencode('{"channel": "'.$wgSlackChannel.'",
                     "username": "'.$wgSlackUserName.'",
                     "text": "'.$message.
                   '"}');

      // POST it to Slack.
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      $result = curl_exec($ch);
      wfDebug("Slack Result: ".$result."\n");
    }

}
