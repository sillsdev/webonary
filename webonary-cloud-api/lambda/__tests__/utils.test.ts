
import { isFieldWorksVersionOK } from '../utils';

describe('methodAuthorize', () => {

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
