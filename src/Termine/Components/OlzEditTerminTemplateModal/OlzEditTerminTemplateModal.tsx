import * as bootstrap from 'bootstrap';
import React from 'react';
import {useForm, SubmitHandler, Resolver, FieldErrors} from 'react-hook-form';
import {olzApi} from '../../../Api/client';
import {OlzMetaData, OlzTerminLabelData, OlzTerminTemplateData} from '../../../Api/client/generated_olz_api_types';
import {OlzEntityField} from '../../../Components/Common/OlzEntityField/OlzEntityField';
import {OlzTextField} from '../../../Components/Common/OlzTextField/OlzTextField';
import {OlzMultiFileField} from '../../../Components/Upload/OlzMultiFileField/OlzMultiFileField';
import {OlzMultiImageField} from '../../../Components/Upload/OlzMultiImageField/OlzMultiImageField';
import {getApiBoolean, getApiNumber, getApiString, getFormBoolean, getFormNumber, getFormString, getResolverResult, validateIntegerOrNull, validateTimeOrNull} from '../../../Utils/formUtils';
import {Entity, isDefined} from '../../../Utils/generalUtils';
import {initReact} from '../../../Utils/reactUtils';

import './OlzEditTerminTemplateModal.scss';

interface OlzEditTerminTemplateForm {
    startTime: string;
    durationSeconds: string;
    title: string;
    text: string;
    link: string;
    deadlineEarlierSeconds: string;
    deadlineTime: string;
    hasNewsletter: string|boolean;
    types: (string|boolean)[];
    locationId: number|null;
    imageIds: string[];
    fileIds: string[];
}

const resolver: Resolver<OlzEditTerminTemplateForm> = async (values) => {
    const errors: FieldErrors<OlzEditTerminTemplateForm> = {};
    [errors.startTime, values.startTime] = validateTimeOrNull(values.startTime);
    errors.durationSeconds = validateIntegerOrNull(values.durationSeconds);
    errors.deadlineEarlierSeconds = validateIntegerOrNull(values.deadlineEarlierSeconds);
    [errors.deadlineTime, values.deadlineTime] = validateTimeOrNull(values.deadlineTime);
    return getResolverResult(errors, values);
};

function getFormFromApi(labels: Entity<OlzTerminLabelData>[], apiData?: OlzTerminTemplateData): OlzEditTerminTemplateForm {
    const typesSet = new Set(apiData?.types ?? []);
    return {
        startTime: getFormString(apiData?.startTime),
        durationSeconds: getFormNumber(apiData?.durationSeconds),
        title: getFormString(apiData?.title),
        text: getFormString(apiData?.text),
        link: getFormString(apiData?.link),
        deadlineEarlierSeconds: getFormNumber(apiData?.deadlineEarlierSeconds),
        deadlineTime: getFormString(apiData?.deadlineTime),
        hasNewsletter: getFormBoolean(apiData?.newsletter),
        types: labels.map((label) => getFormBoolean(typesSet.has(label.data.ident))),
        locationId: apiData?.locationId ?? null,
        fileIds: apiData?.fileIds ?? [],
        imageIds: apiData?.imageIds ?? [],
    };
}

function getApiFromForm(labels: Entity<OlzTerminLabelData>[], formData: OlzEditTerminTemplateForm): OlzTerminTemplateData {
    const typesSet = new Set(labels
        .map((label, index) => (
            getApiBoolean(formData.types[index]) ? label.data.ident : undefined
        ))
        .filter(isDefined));
    return {
        startTime: getApiString(formData.startTime),
        durationSeconds: getApiNumber(formData.durationSeconds),
        title: getApiString(formData.title) ?? '',
        text: getApiString(formData.text) ?? '',
        link: getApiString(formData.link) ?? '',
        deadlineEarlierSeconds: getApiNumber(formData.deadlineEarlierSeconds),
        deadlineTime: getApiString(formData.deadlineTime),
        newsletter: getApiBoolean(formData.hasNewsletter),
        types: Array.from(typesSet),
        locationId: formData.locationId,
        fileIds: formData.fileIds,
        imageIds: formData.imageIds,
    };
}

// ---

interface OlzEditTerminTemplateModalProps {
    id?: number;
    labels: Entity<OlzTerminLabelData>[];
    meta?: OlzMetaData;
    data?: OlzTerminTemplateData;
}

export const OlzEditTerminTemplateModal = (props: OlzEditTerminTemplateModalProps): React.ReactElement => {
    const {register, handleSubmit, formState: {errors}, control} = useForm<OlzEditTerminTemplateForm>({
        resolver,
        defaultValues: getFormFromApi(props.labels, props.data),
    });

    const [isLocationLoading, setIsLocationLoading] = React.useState<boolean>(false);
    const [isImagesLoading, setIsImagesLoading] = React.useState<boolean>(false);
    const [isFilesLoading, setIsFilesLoading] = React.useState<boolean>(false);
    const [successMessage, setSuccessMessage] = React.useState<string>('');
    const [errorMessage, setErrorMessage] = React.useState<string>('');

    const onSubmit: SubmitHandler<OlzEditTerminTemplateForm> = async (values) => {
        const meta: OlzMetaData = {
            ownerUserId: null,
            ownerRoleId: null,
            onOff: true,
        };
        const data = getApiFromForm(props.labels, values);

        const [err, response] = await (props.id
            ? olzApi.getResult('updateTerminTemplate', {id: props.id, meta, data})
            : olzApi.getResult('createTerminTemplate', {meta, data}));
        if (err || response.status !== 'OK') {
            setSuccessMessage('');
            setErrorMessage(`Anfrage fehlgeschlagen: ${JSON.stringify(err || response)}`);
            return;
        }

        setSuccessMessage('Änderung erfolgreich. Bitte warten...');
        setErrorMessage('');
        // TODO: This could probably be done more smoothly!
        window.location.reload();
    };

    const dialogTitle = (props.id === undefined
        ? 'Termin-Vorlage erstellen'
        : 'Termin-Vorlage bearbeiten'
    );
    const isLoading = isLocationLoading || isImagesLoading || isFilesLoading;

    return (
        <div className='modal fade' id='edit-termin-template-modal' tabIndex={-1} aria-labelledby='edit-termin-template-modal-label' aria-hidden='true'>
            <div className='modal-dialog'>
                <div className='modal-content'>
                    <form className='default-form' onSubmit={handleSubmit(onSubmit)}>
                        <div className='modal-header'>
                            <h5 className='modal-title' id='edit-termin-template-modal-label'>
                                {dialogTitle}
                            </h5>
                            <button type='button' className='btn-close' data-bs-dismiss='modal' aria-label='Schliessen'></button>
                        </div>
                        <div className='modal-body'>
                            <div className='row'>
                                <div className='col mb-3'>
                                    <OlzTextField
                                        title='Beginn Zeit'
                                        name='startTime'
                                        errors={errors}
                                        register={register}
                                    />
                                </div>
                                <div className='col mb-3'>
                                    <OlzTextField
                                        title='Dauer (in Sekunden)'
                                        name='durationSeconds'
                                        errors={errors}
                                        register={register}
                                    />
                                </div>
                            </div>
                            <div className='mb-3'>
                                <OlzTextField
                                    title='Titel'
                                    name='title'
                                    errors={errors}
                                    register={register}
                                />
                            </div>
                            <div className='mb-3'>
                                <OlzTextField
                                    mode='textarea'
                                    title='Text'
                                    name='text'
                                    errors={errors}
                                    register={register}
                                />
                            </div>
                            <div className='mb-3'>
                                <OlzTextField
                                    mode='textarea'
                                    title='Link'
                                    name='link'
                                    errors={errors}
                                    register={register}
                                />
                            </div>
                            <div className='row'>
                                <div className='col mb-3'>
                                    <OlzTextField
                                        title='Meldeschluss vorher (in Sekunden)'
                                        name='deadlineEarlierSeconds'
                                        errors={errors}
                                        register={register}
                                    />
                                </div>
                                <div className='col mb-3'>
                                    <OlzTextField
                                        title='Meldeschluss Zeit'
                                        name='deadlineTime'
                                        errors={errors}
                                        register={register}
                                    />
                                </div>
                            </div>
                            <div className='mb-3'>
                                <input
                                    type='checkbox'
                                    value='yes'
                                    {...register('hasNewsletter')}
                                    id='hasNewsletter-input'
                                />
                                <label htmlFor='hasNewsletter-input'>
                                    Newsletter für Änderung
                                </label>
                            </div>
                            <div className='mb-3'>
                                <label htmlFor='types-container'>Typ</label>
                                <div id='types-container'>
                                    {props.labels?.map((label, index) => (
                                        <span className='types-option'>
                                            <input
                                                type='checkbox'
                                                value='yes'
                                                {...register(`types.${index}`)}
                                                id={`types-${label.data.ident}-input`}
                                                key={label.id}
                                            />
                                            <label htmlFor={`types-${label.data.ident}-input`}>
                                                {label.data.name}
                                            </label>
                                        </span>
                                    ))}
                                </div>
                            </div>
                            <div className='row'>
                                <div className='col mb-3'>
                                    <OlzEntityField
                                        title='Ort'
                                        entityType='TerminLocation'
                                        name='locationId'
                                        errors={errors}
                                        control={control}
                                        setIsLoading={setIsLocationLoading}
                                        nullLabel={'Kein Termin-Ort ausgewählt'}
                                    />
                                </div>
                                <div className='col mb-3'>
                                </div>
                            </div>
                            <div id='images-upload'>
                                <OlzMultiImageField
                                    title='Bilder'
                                    name='imageIds'
                                    errors={errors}
                                    control={control}
                                    setIsLoading={setIsImagesLoading}
                                />
                            </div>
                            <div id='files-upload'>
                                <OlzMultiFileField
                                    title='Dateien'
                                    name='fileIds'
                                    errors={errors}
                                    control={control}
                                    setIsLoading={setIsFilesLoading}
                                />
                            </div>
                            <div className='success-message alert alert-success' role='alert'>
                                {successMessage}
                            </div>
                            <div className='error-message alert alert-danger' role='alert'>
                                {errorMessage}
                            </div>
                        </div>
                        <div className='modal-footer'>
                            <button type='button' className='btn btn-secondary' data-bs-dismiss='modal'>Abbrechen</button>
                            <button
                                type='submit'
                                disabled={isLoading}
                                className='btn btn-primary'
                                id='submit-button'
                            >
                                Speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export function initOlzEditTerminTemplateModal(
    id?: number,
    meta?: OlzMetaData,
    data?: OlzTerminTemplateData,
): boolean {
    olzApi.call('listTerminLabels', {}).then((response) => {
        initReact('edit-entity-react-root', (
            <OlzEditTerminTemplateModal
                id={id}
                labels={response.items}
                meta={meta}
                data={data}
            />
        ));
        window.setTimeout(() => {
            const modal = document.getElementById('edit-termin-template-modal');
            if (modal) {
                new bootstrap.Modal(modal, {backdrop: 'static'}).show();
            }
        }, 1);
    });
    return false;
}
