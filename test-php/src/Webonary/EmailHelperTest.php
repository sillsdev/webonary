<?php

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\EmailHelper;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\EmailHelper
 * @noinspection PhpUndefinedNamespaceInspection
 */
class EmailHelperTest extends WP_UnitTestCase
{
	public function testSetCommentNotificationReplyTo()
	{
		$user = get_user_by('id', 1);
		$post_id = self::factory()->post->create(['post_author' => 1]);
		$comment_id = self::factory()->comment->create(
			[
				'comment_post_ID' => $post_id,
				'user_id' => 1,
				'comment_author' => $user->display_name,
				'comment_author_email' => $user->user_email,
			]
		);

		// header is empty
		$result = EmailHelper::SetCommentNotificationReplyTo('', $comment_id);
		$this->assertEquals("Reply-To: \"$user->user_email\" <$user->user_email>\n", $result);

		// header already has Reply-To
		$header = "Reply-To: \"Unit Test\" <unit_test@example.org>\n";
		$result = EmailHelper::SetCommentNotificationReplyTo($header, $comment_id);
		$this->assertEquals($header, $result);

		// no comment found
		$result = EmailHelper::SetCommentNotificationReplyTo('', 0);
		$this->assertEquals('', $result);
	}

	public function testApplyFilter()
	{
		$user = get_user_by('id', 1);
		$post_id = self::factory()->post->create(['post_author' => 1]);
		$comment_id = self::factory()->comment->create(
			[
				'comment_post_ID' => $post_id,
				'user_id' => 1,
				'comment_author' => $user->display_name,
				'comment_author_email' => $user->user_email,
			]
		);


		$result = apply_filters('comment_notification_headers', '', $comment_id);
		$this->assertEquals("Reply-To: \"$user->user_email\" <$user->user_email>\n", $result);

		$result = apply_filters('comment_moderation_headers', '', $comment_id);
		$this->assertEquals("Reply-To: \"$user->user_email\" <$user->user_email>\n", $result);
	}
}
