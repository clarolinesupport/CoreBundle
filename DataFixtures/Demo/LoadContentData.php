<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class LoadContentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $titles = array(
            '',
            'ClarolineConnect© : plateforme Claroline de nouvelle génération.',
            '',
            'ClarolineConnect© Demo',
            'Youtube',
            'Vimeo',
            'Simple Website',
            'Wikipedia'
        );

        $textDir = __DIR__. '/files/homepage';

        $texts = array(
            'http://fr.slideshare.net/batier/claroline-connect',
            file_get_contents("{$textDir}/text1.txt", 'r'),
            'http://www.youtube.com/watch?v=4mlWeQed0_I',
            file_get_contents("{$textDir}/text4.txt", 'r'),
            'http://youtu.be/4mlWeQed0_I',
            'http://vimeo.com/63773788',
            'http://www.opengraph.be/',
            'http://fr.wikipedia.org/wiki/Claroline'
        );

        $types = array('home', 'home', 'home', 'home', 'opengraph', 'opengraph', 'opengraph', 'opengraph');
        $sizes = array(
            'content-5', 'content-7', 'content-7', 'content-5', 'content-12', 'content-12', 'content-12', 'content-12'
        );

        foreach ($titles as $i => $title) {
            $type = $manager->getRepository('ClarolineCoreBundle:Home\Type')->findOneBy(array('name' => $types[$i]));

            $content[$i] = new Content();
            $content[$i]->setTitle($title);
            $content[$i]->setContent($texts[$i]);

            $first = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type')->findOneBy(
                array('back' => null, 'type' => $type)
            );

            $contentType = new Content2Type($first);

            $contentType->setContent($content[$i]);
            $contentType->setType($type);
            $contentType->setSize($sizes[$i]);

            $manager->persist($contentType);
            $manager->persist($content[$i]);

            $manager->flush();
        }
    }
}
