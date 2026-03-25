import {getFieldWorksVersion} from '../utils';

describe('test utils functions', () => {

  test('getFieldWorksVersion - version is set', () => {

    let headers: any = {'user-agent': 'FieldWorks ver 9.8.7'};
    let ver_parts: any = getFieldWorksVersion(headers);
    expect(ver_parts.length).toEqual(3);
    expect(ver_parts[0]).toEqual(9);
    expect(ver_parts[1]).toEqual(8);
    expect(ver_parts[2]).toEqual(7);

    headers = {'user-agent': 'FieldWorks ver 9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts.length).toEqual(3);
    expect(ver_parts[0]).toEqual(9);
    expect(ver_parts[1]).toEqual(9);
    expect(ver_parts[2]).toEqual('0-alpha1');

    headers = {'user-agent': 'FieldWorks alpha'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts).toBeNull();

    headers = {'user-agent': '9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts).toBeNull();

    headers = {'unknown': 'FieldWorks ver 9.9.0-alpha1'};
    ver_parts = getFieldWorksVersion(headers);
    expect(ver_parts).toBeNull();
  });
});
