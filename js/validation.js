const validation = new JustValidate('#form-signup');

validation
  .addField('#username-signup', [
    {
      rule: 'required',
      errorMessage: 'Username is required',
    },
    {
      rule: 'minLength',
      value: 3,
      errorMessage: 'Käyttäjänimen tulee olla vähintään 3 merkkiä',
    },
    {
      rule: 'maxLength',
      value: 20,
      errorMessage: 'Käyttäjänimen tulee olla enintään 20 merkkiä',
    },
  ])
  .addField('#password-signup', [
    {
      rule: 'required',
      errorMessage: 'Password is required',
    },
  ])
  .addField('#email-signup', [
    {
      rule: 'required',
      errorMessage: 'Email is required',
    },
    {
      rule: 'email',
      errorMessage: 'Email is not valid',
    },
  ])
  .onSuccess((event) => {
    event.target.submit();
  });