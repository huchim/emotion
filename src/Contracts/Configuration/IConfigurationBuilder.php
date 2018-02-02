<?php namespace Emotion\Contracts\Configuration;

interface IConfigurationBuilder {
    /**
     * Undocumented function
     *
     * @param IConfigurationSource $source
     * @return IConfigurationBuilder
     */
    public function add(IConfigurationSource $source);

    /**
     * Undocumented function
     *
     * @return IConfigurationRoot
     */
    public function build();

    /**
     * Undocumented function
     *
     * @return IConfigurationSource[]
     */
    public function getSources();
}