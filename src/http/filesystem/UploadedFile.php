<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\http\filesystem;

use og\error\FileException;

class UploadedFile extends File
{

    private $test = false;
    private $originalName;
    private $mimeType;
    private $error;

    public function __construct($path, $originalName, $mimeType = null, $error = null, $test = false)
    {
        $this->originalName = $originalName;
        $this->mimeType     = $mimeType ?: 'application/octet-stream';
        $this->test         = $test;
        $this->error        = $error ?: UPLOAD_ERR_OK;

        parent::__construct($path, UPLOAD_ERR_OK === $this->error);
    }

    public function isValid()
    {
        $isOk = UPLOAD_ERR_OK === $this->error;

        return $this->test ? $isOk : $isOk && is_uploaded_file($this->getPathname());
    }

    /**
     * 上传文件
     * @access public
     * @param string      $directory 保存路径
     * @param string|null $name      保存的文件名
     * @return File
     */
    public function move($directory, $name = null)
    {
        if ($this->isValid()) {
            if ($this->test) {
                return parent::move($directory, $name);
            }

            $target = $this->getTargetFile($directory, $name);

            set_error_handler(function ($type, $msg) use (&$error) {
                $error = $msg;
            });

            $moved = move_uploaded_file($this->getPathname(), $target);
            restore_error_handler();
            if (!$moved) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error)));
            }

            @chmod($target, 0666 & ~umask());

            return $target;
        }

        throw new FileException($this->getErrorMessage());
    }

    /**
     * 获取错误信息
     * @access public
     * @return string
     */
    protected function getErrorMessage()
    {
        switch ($this->error) {
            case 1:
            case 2:
                $message = 'upload File size exceeds the maximum value';
                break;
            case 3:
                $message = 'only the portion of file is uploaded';
                break;
            case 4:
                $message = 'no file to uploaded';
                break;
            case 6:
                $message = 'upload temp dir not found';
                break;
            case 7:
                $message = 'file write error';
                break;
            default:
                $message = 'unknown upload error';
        }

        return $message;
    }

    /**
     * 获取上传文件类型信息
     * @return string
     */
    public function getOriginalMime()
    {
        return $this->mimeType;
    }

    /**
     * 上传文件名
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * 获取上传文件扩展名
     * @return string
     */
    public function getOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    public function extension()
    {
        return $this->getOriginalExtension();
    }

}
