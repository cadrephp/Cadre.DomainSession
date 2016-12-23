<?php
namespace Cadre\Domain_Session;

class DomainSessionStorageFiles implements DomainSessionStorageInterface
{
    protected $idFactory;
    protected $path;
    protected $cacheExpire;

    public function __construct(IdFactoryInterface $idFactory, $path, $cacheExpire = 180)
    {
        $this->idFactory = $idFactory;
        $this->path = $path;
        $this->cacheExpire = $cacheExpire;
    }

    public function newId()
    {
        do {
            $id = ($this->idFactory)();
        } while ($this->exists($id));

        return $id;
    }

    public function read($id)
    {
        if ($this->isExpired($id)) {
            $this->delete($id);
        }

        $file = $this->getFile($id);

        if (file_exists($file)) {
            return $this->unserialize(file_get_contents($file));
        }

        return [];
    }

    public function write($id, array $data)
    {
        $file = $this->getFile($id);

        file_put_contents(
            $file,
            $this->serialize($data)
        );
    }

    public function rename($oldId, $newId)
    {
        $oldFile = $this->getFile($oldId);
        $newFile = $this->getFile($newId);

        if (!file_exists($oldFile)) {
            throw new DomainSessionException("Session {$oldId} doesn't exist");
        }

        if ($this->isExpired($newId)) {
            $this->delete($newId);
        } elseif (file_exists($newFile)) {
            throw new DomainSessionException("Session {$newId} already exists");
        }

        rename($oldFile, $newFile);
    }

    public function delete($id)
    {
        unlink($this->getFile($id));
    }

    public function exists($id)
    {
        return file_exists($this->getFile($id));
    }

    protected function getFile($id)
    {
        // Sanitizing id for filename
        return $this->path . DIRECTORY_SEPARATOR . preg_replace('![^a-z0-9_\-\.]!i', '_', $id);
    }

    protected function isExpired($id)
    {
        return (
            $this->exists($id) &&
            time() - filectime($this->getFile($id)) >= $this->cacheExpire
        );
    }

    protected function serialize($data)
    {
        return serialize($data);
    }

    protected function unserialize($serial)
    {
        return unserialize($serial);
    }
}
