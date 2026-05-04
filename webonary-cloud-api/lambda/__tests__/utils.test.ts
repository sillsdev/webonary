import {getFieldWorksVersion} from '../utils';
import { isFieldWorksVersionOK } from '../utils';

describe('test utils functions', () => {

  test('getFieldWorksVersion - version is set', () => {

    let headers: any = {'User-Agent': 'FieldWorks ver 9.8.7.1234'};
    let ver_parts: any = getFieldWorksVersion(headers);
    expect(ver_parts.length).toEqual(4);
    expect(ver_parts[0]).toEqual(9);
    expect(ver_parts[1]).toEqual(8);
    expect(ver_parts[2]).toEqual(7);
    expect(ver_parts[3]).toEqual(1234);

    headers = {'User-Agent': 'FieldWorks ver 9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts.length).toEqual(3);
    expect(ver_parts[0]).toEqual(9);
    expect(ver_parts[1]).toEqual(9);
    expect(ver_parts[2]).toEqual('0-alpha1');

    headers = {'User-Agent': 'FieldWorks alpha'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts[0]).toEqual('FieldWorks alpha');
    // expect(ver_parts).toBeNull();

    headers = {'User-Agent': '9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts[0]).toEqual('9.9.0-alpha1');
    // expect(ver_parts).toBeNull();

    headers = {'unknown': 'FieldWorks ver 9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts).toBeNull();
  });

  test('isFieldWorksVersionOK: not FieldWorks', () => {

    let headers: any = null;
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = {};
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': '' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': 'Unit Testing' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': 'Unit Testing' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();
  });

  test('isFieldWorksVersionOK: FieldWorks not valid', () => {

    let headers = { 'user-agent': 'FieldWorks' };
    expect(isFieldWorksVersionOK(headers)).toBeFalsy();

    headers = { 'user-agent': 'FieldWorks Language Explorer v.0.0.0' };
    expect(isFieldWorksVersionOK(headers)).toBeFalsy();

    headers = { 'user-agent': 'FieldWorks Language Explorer v.9.2.4' };
    expect(isFieldWorksVersionOK(headers)).toBeFalsy();
  });

  test('isFieldWorksVersionOK: FieldWorks is valid', () => {

    let headers = { 'user-agent': 'FieldWorks Language Explorer v.9.2.5' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': 'FieldWorks Language Explorer v.9.3' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': 'FieldWorks Language Explorer v.10' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();

    headers = { 'user-agent': 'FieldWorks Language Explorer v.10.0.1' };
    expect(isFieldWorksVersionOK(headers)).toBeTruthy();
  });
});
