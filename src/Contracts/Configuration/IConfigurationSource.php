<?php namespace Emotion\Contracts\Configuration;

interface IConfigurationSource {
    /**
     * Undocumented function
     *
     * @param IConfigurationBuilder $builder
     * @return IConfigurationProvider
     */
    public function build(IConfigurationBuilder $builder);
}
