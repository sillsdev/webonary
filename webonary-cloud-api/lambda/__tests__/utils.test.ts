import {getFieldWorksVersion} from '../utils';

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
});
