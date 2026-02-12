<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Imageenhan\V20190930\Models;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;

class ImitatePhotoStyleAdvanceRequest extends Model
{
    /**
     * @var Stream
     */
    public $imageURLObject;

    /**
     * @var string
     */
    public $styleUrl;
    protected $_name = [
        'imageURLObject' => 'ImageURL',
        'styleUrl'       => 'StyleUrl',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->imageURLObject) {
            $res['ImageURL'] = $this->imageURLObject;
        }
        if (null !== $this->styleUrl) {
            $res['StyleUrl'] = $this->styleUrl;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return ImitatePhotoStyleAdvanceRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['ImageURL'])) {
            $model->imageURLObject = $map['ImageURL'];
        }
        if (isset($map['StyleUrl'])) {
            $model->styleUrl = $map['StyleUrl'];
        }

        return $model;
    }
}
