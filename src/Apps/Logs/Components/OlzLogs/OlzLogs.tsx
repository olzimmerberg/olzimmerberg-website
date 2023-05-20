import React from 'react';

import {olzApi} from '../../../../Api/client';
import {OlzLogLevel, OlzLogsQuery} from '../../../../Api/client/generated_olz_api_types';
import {OlzInfiniteScroll, OlzInfiniteScrollProps} from '../OlzInfiniteScroll/OlzInfiniteScroll';
import {isoNow} from '../../../../Utils/constants';

import './OlzLogs.scss';

const MAX_LINE_LENGTH = 5000;

const getQuery = (channelArg: string, dateArg: string, logLevelArg: string, textSearchArg: string): OlzLogsQuery => {
    let targetDate: string|null = null;
    let firstDate: string|null = null;
    let lastDate: string|null = null;
    let match: RegExpExecArray|null;
    const isoRegex = /^\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s*$/i;
    if (/^\s*(now|jetzt)\s*$/i.exec(dateArg)) {
        targetDate = (new Date()).toISOString();
    } else if (/^\s*(today|heute)\s*$/i.exec(dateArg)) {
        const todayIso = (new Date()).toISOString().substring(0, 10);
        firstDate = `${todayIso} 00:00:00`;
        lastDate = `${todayIso} 23:59:59`;
    } else if ((match = isoRegex.exec(dateArg))) {
        targetDate = match[1] ?? null;
    }

    const logLevelMap: {[key: string]: OlzLogLevel} = {
        'levels-all': null,
        'levels-info-higher': 'info',
        'levels-notice-higher': 'notice',
        'levels-warning-higher': 'warning',
        'levels-error-higher': 'error',
        'levels-critical-higher': 'critical',
        'levels-alert-higher': 'alert',
        'levels-emergency-higher': 'emergency',
    };
    const minLogLevel: OlzLogLevel = logLevelMap[logLevelArg] ?? null;

    const textSearch = textSearchArg || null;

    return {
        channel: channelArg,
        pageToken: null,
        targetDate,
        firstDate,
        lastDate,
        minLogLevel,
        textSearch,
    };
};

const getTokenQuery = (
    initialQuery: OlzLogsQuery,
    pageToken: string|null,
): OlzLogsQuery|null => (pageToken ? {
    ...initialQuery,
    pageToken,
} : null);

export const OlzLogs = (): React.ReactElement => {
    const channels = (window as unknown as {
        olzLogsChannels: {[id: string]: string}
    }).olzLogsChannels;

    const [channel, setChannel] = React.useState<string>(Object.keys(channels)[0]);
    const [date, setDate] = React.useState<string>(isoNow);
    const [logLevel, setLogLevel] = React.useState<string>('levels-all');
    const [textSearch, setTextSearch] = React.useState<string>('');

    const initialQuery = getQuery(channel, date, logLevel, textSearch);

    const fetch: OlzInfiniteScrollProps<string, OlzLogsQuery>['fetch'] =
        async (query: OlzLogsQuery) => {
            const response = await olzApi.call(
                'getLogs',
                {query},
            );
            return {
                items: response.content,
                prevQuery: getTokenQuery(initialQuery, response.pagination.previous),
                nextQuery: getTokenQuery(initialQuery, response.pagination.next),
            };
        };

    const renderItem = (line: string) => {
        if (line === '---') {
            return (
                <div className='log-line' id='initial-position'>
                    <div id='initial-scroll'></div>
                    {line}
                </div>
            );
        }

        const mustCrop = line.length > MAX_LINE_LENGTH;
        const croppedLine = mustCrop
            ? `${line.substring(0, MAX_LINE_LENGTH - 1)}\u{2026}`
            : line;
        const shouldGreyOut = shouldLineBeGreyedOut(line);
        if (shouldGreyOut) {
            return (
                <div className='log-line greyed-out'>
                    {croppedLine}
                </div>
            );
        }

        const formattingRegex = /^(.*\s+)(\S+)\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)(.*)/;
        const match = formattingRegex.exec(croppedLine);
        if (!match) {
            return (<div className='log-line level-unknown'>{croppedLine}</div>);
        }
        const lineLogLevel = match[3].toLowerCase();
        return (
            <div className={`log-line level-${lineLogLevel}`}>
                {match[1]}
                <span className='log-channel'>
                    {match[2]}
                </span>
                    .
                <span className='log-level'>
                    {match[3]}
                </span>
                {match[4]}
            </div>
        );
    };

    return (<>
        <div className='logs-header'>
            <select
                id='log-channel-select'
                className='form-control form-select form-select-sm'
                value={channel}
                onChange={(e) => {
                    const select = e.target;
                    const newLogChannel = select.options[select.selectedIndex].value;
                    setChannel(newLogChannel);
                }}
            >
                {Object.keys(channels).map((id) => (
                    <option value={id}>{channels[id]}</option>
                ))}
            </select>
            <div className='input-group input-group-sm'>
                <div className='input-group-prepend input-group-text input-group-text-sm'>
                    <img src='/assets/icns/calendar.svg' className='noborder icon' />
                </div>
                <input
                    type='text'
                    id='date-filter-input'
                    className='form-control form-control-sm'
                    value={date}
                    onChange={(e) => {
                        const newDate = e.target.value;
                        setDate(newDate);
                    }}
                />
            </div>
            <select
                id='log-level-filter-select'
                className='form-control form-select form-select-sm'
                value={logLevel}
                onChange={(e) => {
                    const select = e.target;
                    const newLogLevel = select.options[select.selectedIndex].value;
                    setLogLevel(newLogLevel);
                }}
            >
                <option value='levels-all'>Alle Log-Levels</option>
                <option value='levels-info-higher'>"Info" & höher</option>
                <option value='levels-notice-higher'>"Notice" & höher</option>
                <option value='levels-warning-higher'>"Warning" & höher</option>
                <option value='levels-error-higher'>"Error" & höher</option>
                <option value='levels-critical-higher'>"Critical" & höher</option>
                <option value='levels-alert-higher'>"Alert" & höher</option>
                <option value='levels-emergency-higher'>"Emergency" & höher</option>
            </select>
            <div className='input-group input-group-sm'>
                <div className='input-group-prepend input-group-text input-group-text-sm'>
                    <img src='/assets/icns/magnifier_16.svg' className='noborder icon' />
                </div>
                <input
                    type='text'
                    id='text-search-filter-input'
                    className='form-control form-control-sm'
                    placeholder='Suche...'
                    value={textSearch}
                    onChange={(e) => {
                        const newTextSearch = e.target.value;
                        setTextSearch(newTextSearch);
                    }}
                />
            </div>
        </div>
        <div id='logs'>
            <OlzInfiniteScroll
                initialQuery={initialQuery}
                fetch={fetch}
                renderItem={renderItem}
            />
        </div>
    </>);
};

export function shouldLineBeGreyedOut(line: string): boolean {
    const monitorCommandRegex = /\s+Olz\\Command\\Monitor/;
    const isMonitorCommand = !!monitorCommandRegex.exec(line);
    const isAccessRuleViolation = line.includes('access forbidden by rule');
    return isMonitorCommand || isAccessRuleViolation;
}

// $(() => {
//     olzLogsFetch();
//     olzLogsGetFirstLog();
//     olzLogsLevelFilterChange();
// });
