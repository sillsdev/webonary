<?php

namespace SIL\Webonary\Helpers;

class EmailHelper
{
	public static function SetCommentNotificationReplyTo(string $headers, int $comment_id): string
	{
		if (str_contains($headers, 'Reply-To:'))
			return $headers;

		$comment = get_comment($comment_id);
		if (empty($comment))
			return $headers;

		$headers .= "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>\n";

		return $headers;
	}
}
