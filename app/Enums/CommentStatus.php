<?php

namespace App\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
}
