<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\ExtGroup\PdfGroup;

require __DIR__ . '/../../../../../vendor/autoload.php';

use Espo\Core\Exceptions\Error;
use mikehaertl\pdftk\Pdf;

#
# arg1 - for entity
# arg2 - for entity-id
# arg3 - document-id
# arg4 - filename
# and arg2 .. n: field, value, etc. 
#

class FillinType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        $this->addDependency('entityManager');
        $this->addDependency('serviceFactory');
    }

    public function process(\StdClass $item)
    {
        $args = $this->fetchArguments($item);

        if (count($args) < 4) throw new Error("Formula ext\\pdf\\fillin: Too few arguments.");

        $entity = array_shift($args);
        $entity_id = array_shift($args);
	    $document_id = array_shift($args);
	    $filename = array_shift($args);

        $fields = [];
        while(count($args) > 0) {
            $field = array_shift($args);
            $value = array_shift($args);
            $fields[$field] = $value;
        }

        $em = $this->getInjection('entityManager');

        $document = $em->getEntity('Document', $document_id);

        if (!$document) {
            $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: document {$document_id} does not exist.");
            $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: file identifier assumed.");
            $file_id = $document_id;
        } else {
	    $file_id = $document->get('fileId');
        }
        $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: file_id: {$file_id}");

        if ($filename) {
            if (substr($fileName, -4) !== '.pdf') {
                $fileName .= '.pdf';
            }
        } else {
            $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: filename must be given.");
            return null;
        }
        
        $attachment = $em->getEntity('Attachment', $file_id);
        if (!$attachment) {
            $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: filename not found.");
            return null;
        }

	    $pdf_in_filename = $em->getRepository('Attachment')->getFilePath($attachment);
        $tmpdir = sys_get_temp_dir();
        $pdf_out_filename = "$tmpdir/$filename";

        $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: pdf file in: {$pdf_in_filename}");

        $pdf_errors = [];
        $pdf = new Pdf($pdf_in_filename);
        array_push($pdf_errors, 'ext\\pdf\\fillin: new Pdf: ' . $pdf->getError());
        $pdf->fillForm($fields);
        array_push($pdf_errors, 'ext\\pdf\\fillin: fillForm: ' . $pdf->getError());
        $pdf->needAppearances();
        array_push($pdf_errors, 'ext\\pdf\\fillin: needAppearances: ' . $pdf->getError());
        $pdf->saveAs($pdf_out_filename);
        array_push($pdf_errors, 'ext\\pdf\\fillin: saveAs: ' . $pdf->getError());

        if (file_exists($pdf_out_filename)) {
            $contents = file_get_contents($pdf_out_filename);
            unlink($pdf_out_filename);

            $attachment = $em->createEntity('Attachment', [
                'name' => $filename,
                'type' => 'application/pdf',
                'contents' => $contents,
                'relatedId' => $entity_id,
                'relatedType' => $entity,
                'role' => 'Attachment',
            ]);

            return $attachment->id;
       } else {
            $GLOBALS['log']->warning("Formula ext\\pdf\\fillin: output filename {$pdf_out_filename} not found.");
            foreach($pdf_errors as $err) {
                $GLOBALS['log']->warning($err);
            }
            return null;
       }
    }
}
